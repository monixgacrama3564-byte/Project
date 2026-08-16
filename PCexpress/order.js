// order.js  v1.0
// ─────────────────────────────────────────────────────────────
// Firestore order placement for PC Express.
// Exported:  placeOrder(orderData) → { success, orderNumber, orderId }
//
// Called by: checkout_firestore_bridge.js
// Collections written:
//   orders/{orderId}
//   orders/{orderId}/items/{itemId}
//   carts/{uid}/items  (cleared after success)
// ─────────────────────────────────────────────────────────────

import { auth, db } from "./firebase-config.js";
import {
  collection,
  doc,
  addDoc,
  getDocs,
  deleteDoc,
  serverTimestamp,
  writeBatch,
} from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

/** Generate a readable order number: PCX-YYYYMMDD-XXXXXX */
function generateOrderNumber() {
  const timestamp = Date.now().toString().slice(-8);
  const random = Math.floor(Math.random() * 10000)
    .toString()
    .padStart(4, "0");
  return `PCX-${timestamp}-${random}`;
}

/** Round to 2 decimal places */
function round2(n) {
  return Math.round((parseFloat(n) || 0) * 100) / 100;
}

// ─────────────────────────────────────────────────────────────
// VALIDATION
// ─────────────────────────────────────────────────────────────
function validate(data) {
  const { mode, cartItems, payment, firstName, lastName, city, address, guestEmail } = data;

  if (!cartItems || cartItems.length === 0) {
    return "Cart is empty.";
  }

  if (mode === "ship") {
    if (!firstName || !lastName) return "First and last name are required.";
    if (!address || address.length < 5) return "Please enter a complete street address.";
    if (!city) return "City is required.";
  }

  if (mode === "pickup") {
    if (!firstName || !lastName) return "First and last name are required for pickup.";
  }

  if (!auth.currentUser && !guestEmail) {
    return "Please enter your email address.";
  }

  if (!payment) return "Please select a payment method.";

  return null; // no error
}

// ─────────────────────────────────────────────────────────────
// CLEAR FIRESTORE CART
// ─────────────────────────────────────────────────────────────
async function clearFirestoreCart(uid) {
  try {
    const snap = await getDocs(collection(db, "carts", uid, "items"));
    const batch = writeBatch(db);
    snap.docs.forEach(d => batch.delete(d.ref));
    await batch.commit();
  } catch (err) {
    console.warn("clearFirestoreCart:", err);
  }
}

// ─────────────────────────────────────────────────────────────
// PLACE ORDER
// ─────────────────────────────────────────────────────────────

/**
 * @param {Object} orderData
 *   mode            "ship" | "pickup"
 *   payment         "cod" | "gcash" | "card" | ...
 *   cartItems       Array<{ id, name, price, image, variant, qty }>
 *   shippingFee     number  (0 for pickup)
 *   discount        number
 *   coupon          string
 *   orderNote       string
 *   shippingMethod  "standard" | "express" | "overnight" | "pickup"
 *   guestEmail      string  (guests only)
 *   selectedStore   string  (pickup only)
 *   selectedBranchId string (pickup only)
 *   firstName, lastName, company, address, apartment,
 *   city, province, postalCode, phone, country
 *
 * @returns {Promise<{ success: boolean, orderNumber?: string, orderId?: string, message?: string }>}
 */
export async function placeOrder(orderData) {
  const user = auth.currentUser;
  if (!user) {
    return { success: false, message: "User not authenticated." };
  }

  try {
    const {
      mode = "ship",
      payment = "cod",
      cartItems = [],
      shippingFee = 0,
      discount = 0,
      coupon = "",
      orderNote = "",
      shippingMethod = "standard",
      guestEmail = "",
      selectedStore = "",
      selectedBranchId = "",
      firstName = "",
      lastName = "",
      company = "",
      address = "",
      apartment = "",
      city = "",
      province = "",
      postalCode = "",
      phone = "",
      country = "Philippines",
    } = orderData;

    // ── Validate ──────────────────────────────────────────────
    const validationError = validate({ mode, cartItems, payment, firstName, lastName, city, address, guestEmail });
    if (validationError) {
      return { success: false, message: validationError };
    }

    // ── Totals ────────────────────────────────────────────────
    const subtotal   = round2(cartItems.reduce((sum, item) => sum + (parseFloat(item.price) || parseFloat(item.priceRaw) || 0) * (parseInt(item.qty) || 1), 0));
    const fee        = round2(shippingFee);
    const disc       = round2(discount);
    const grandTotal = round2(Math.max(0, subtotal + fee - disc));

    // ── Order number ──────────────────────────────────────────
    const orderNumber = generateOrderNumber();

    // ── Auth context ──────────────────────────────────────────
    const uid  = user?.uid || null;

    try {
      // ── Write order document ───────────────────────────────
      const orderRef = await addDoc(collection(db, "orders"), {
        orderNumber,
        status:         "pending",
        mode,
        paymentMethod:  payment,
        shippingMethod: mode === "ship" ? shippingMethod : "pickup",

        subtotal,
        shippingFee:    fee,
        discount:       disc,
        couponCode:     coupon || null,
        grandTotal,

        shipFirstName:  firstName,
        shipLastName:   lastName,
        shipCompany:    company   || null,
        shipAddress:    address   || null,
        shipApartment:  apartment || null,
        shipCity:       city      || null,
        shipProvince:   province  || null,
        shipPostalCode: postalCode || null,
        shipPhone:      phone     || null,
        shipCountry:    country   || "Philippines",

        pickupStore:    mode === "pickup" ? (selectedStore   || null) : null,
        branchId:       mode === "pickup" ? (selectedBranchId || null) : null,

        orderNote: orderNote || null,

        userId:     uid,
        guestEmail: uid ? null : (guestEmail || null),

        createdAt: serverTimestamp(),
        updatedAt: serverTimestamp(),
      });

      // ── Write order items sub-collection ──────────────────
      const itemsCol = collection(db, "orders", orderRef.id, "items");
      const itemWritePromises = cartItems.map(item => {
        const qty       = parseInt(item.qty)          || 1;
        const unitPrice = round2(parseFloat(item.priceRaw) || parseFloat(item.price) || 0);
        const lineTotal = round2(unitPrice * qty);

        return addDoc(itemsCol, {
          productId:    item.id    || null,
          productName:  item.name  || "Product",
          productImage: item.image || null,
          variant:      item.variant || null,
          qty,
          unitPrice,
          lineTotal,
        });
      });

      await Promise.all(itemWritePromises);

      // ── Clear Firestore cart (logged-in users only) ───────
      if (uid) {
        await clearFirestoreCart(uid);
      }

      // ── Clear localStorage cart (guests / legacy) ─────────
      try {
        localStorage.removeItem("pcx_cart");
        localStorage.removeItem("pcx_note");
        localStorage.removeItem("pcx_coupon");
      } catch (_) {}

      return {
        success:     true,
        orderNumber,
        orderId:     orderRef.id,
      };

    } catch (err) {
      console.error("placeOrder error:", err);
      return {
        success: false,
        message: err?.message || "Failed to place order. Please try again.",
      };
    }

  } catch (err) {  // ← catches outer try
    console.error("placeOrder fatal error:", err);
    return {
      success: false,
      message: err?.message || "Unexpected error placing order.",
    };
  }
}

export default placeOrder;