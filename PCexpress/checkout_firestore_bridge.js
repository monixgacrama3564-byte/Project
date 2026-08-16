// checkout_firestore_bridge.js  v2.3
// Signal to checkout.php that the bridge is handling cart/auth — prevents duplicate loadCart()
window._bridgeActive = true;

import { auth, db } from "./firebase-config.js";
import { onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";
import { collection, getDocs, doc, deleteDoc } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";
import { placeOrder } from "./order.js";

// ─────────────────────────────────────────────────────────────
// LOAD ADDRESSES FROM FIRESTORE
// ─────────────────────────────────────────────────────────────
async function loadFirestoreAddresses(uid) {
  try {
    const snap = await getDocs(collection(db, "userAddresses", uid, "addresses"));
    return snap.docs
      .map(d => ({ id: d.id, ...d.data() }))
      .sort((a, b) => (b.isDefault ? 1 : 0) - (a.isDefault ? 1 : 0));
  } catch (err) {
    console.warn("loadFirestoreAddresses:", err);
    return [];
  }
}

// Prefill the shipping form with the default address
function prefillShippingForm(addr) {
  if (!addr) return;
  const set = (id, val) => {
    const el = document.getElementById(id);
    if (el && !el.value) el.value = val || "";
  };
  set("fFirst",     addr.firstName);
  set("fLast",      addr.lastName);
  set("fAddress",   addr.address);
  set("fApartment", addr.apartment);
  set("fCity",      addr.city);
  set("fProvince",  addr.province);
  set("fPostal",    addr.postalCode);
  set("fPhone",     addr.phone);
  set("fCountry",   addr.country || "Philippines");

  if (typeof window.checkAddressReady === "function") {
    window.checkAddressReady();
  }
}

// Populate "Saved Addresses" dropdown if it exists
function populateAddressSelect(addresses) {
  const sel = document.getElementById("savedAddressSelect");
  if (!sel || addresses.length === 0) return;
  sel.innerHTML = `<option value="">— Choose a saved address —</option>` +
    addresses.map((a, i) =>
      `<option value="${i}">${a.firstName} ${a.lastName} — ${a.address}, ${a.city}</option>`
    ).join("");
  sel.addEventListener("change", () => {
    const idx = parseInt(sel.value);
    if (!isNaN(idx) && addresses[idx]) prefillShippingForm(addresses[idx]);
  });
  const row = sel.closest(".saved-addr-row");
  if (row) row.style.removeProperty("display");
}

// ─────────────────────────────────────────────────────────────
// PRICE HELPER — always returns a clean float
// ─────────────────────────────────────────────────────────────
function cleanPrice(val) {
  if (typeof val === "number" && !isNaN(val)) return val;
  return parseFloat(String(val || "0").replace(/[^0-9.]/g, "")) || 0;
}

// ─────────────────────────────────────────────────────────────
// LOAD CART — Firestore first, fallback to localStorage
// ─────────────────────────────────────────────────────────────
async function loadFirestoreCart(uid) {
  try {
    const snap = await getDocs(collection(db, "carts", uid, "items"));
    return snap.docs.map(d => {
      const data  = d.data();
      const price = cleanPrice(data.productPrice);
      return {
        id:       d.id,
        name:     data.productName  || "Product",
        price,
        priceRaw: price,
        image:    data.productImage || "",
        variant:  data.variant      || "",
        qty:      parseInt(data.qty) || 1,
      };
    });
  } catch (err) {
    console.warn("loadFirestoreCart:", err);
    return [];
  }
}

function loadLocalStorageCart() {
  try {
    const stored = localStorage.getItem("pcx_cart");
    if (!stored) return [];
    return (JSON.parse(stored) || []).map(item => {
      const price = cleanPrice(item.priceRaw) || cleanPrice(item.price);
      return { ...item, priceRaw: price, price };
    });
  } catch {
    return [];
  }
}

// ─────────────────────────────────────────────────────────────
// REMOVE ITEM FROM CART
// ─────────────────────────────────────────────────────────────
async function removeCartItem(itemId) {
  const user = auth.currentUser;

  // Remove from Firestore if logged in
  if (user) {
    try {
      await deleteDoc(doc(db, "carts", user.uid, "items", itemId));
    } catch (err) {
      console.warn("removeCartItem Firestore:", err);
    }
  }

  // Remove from local state
  let cartItems = window._checkoutCartItems || [];
  cartItems = cartItems.filter(i => i.id !== itemId);

  // Remove from localStorage too
  try {
    const stored = localStorage.getItem("pcx_cart");
    if (stored) {
      const arr = JSON.parse(stored).filter(i => (i.id || i.productId) !== itemId);
      localStorage.setItem("pcx_cart", JSON.stringify(arr));
    }
  } catch (_) {}

  // Re-render
  renderCartSummary(cartItems);
}

// ─────────────────────────────────────────────────────────────
// RENDER ORDER SUMMARY
// ─────────────────────────────────────────────────────────────
function renderCartSummary(cartItems) {
  const listEl  = document.getElementById("summaryItems");
  const subEl   = document.getElementById("summarySubtotal");
  const totalEl = document.getElementById("summaryTotal");
  if (!listEl) return;

  if (!cartItems || cartItems.length === 0) {
    listEl.innerHTML = `<div class="empty-cart-msg">
      <i class="fas fa-shopping-cart"></i>
      Your cart is empty.<br><a href="home.php">Continue shopping</a>
    </div>`;
    if (subEl)   subEl.textContent   = "₱0.00";
    if (totalEl) totalEl.textContent = "₱0.00";
    return;
  }

  const fmt = n => "₱" + n.toLocaleString("en-US", { minimumFractionDigits: 2 });

  // Expose remove function so inline onclick can reach it
  window._removeCartItem = removeCartItem;

  let subtotal = 0;
  listEl.innerHTML = cartItems.map(item => {
    const price     = cleanPrice(item.priceRaw) || cleanPrice(item.price);
    const lineTotal = price * (parseInt(item.qty) || 1);
    subtotal += lineTotal;
    return `
    <div class="summary-item" data-item-id="${item.id}">
      <div class="item-img-wrap">
        <img src="${item.image || 'https://via.placeholder.com/64'}" alt="${item.name || ''}" class="item-img"
             onerror="this.src='https://via.placeholder.com/64'">
        <span class="item-qty-badge">${item.qty || 1}</span>
      </div>
      <div class="item-info">
        <div class="item-name">${item.name || 'Product'}</div>
        ${item.variant ? `<div class="item-variant">${item.variant}</div>` : ""}
      </div>
      <div class="item-price-wrap">
        <div class="item-price">${fmt(lineTotal)}</div>
        <button class="item-remove-btn" onclick="window._removeCartItem('${item.id}')" title="Remove item">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
    </div>`;
  }).join("");

  // Expose cart state to checkout.php scope
  window._checkoutCartItems = cartItems;
  window._checkoutSubtotal  = subtotal;
  try { window.cartItems = cartItems; } catch (_) {}

  // Defer totals update so checkout.php's own JS has finished initializing
  requestAnimationFrame(() => {
    if (typeof window.updateTotals === "function") {
      window.updateTotals(subtotal);
    } else {
      if (subEl)   subEl.textContent   = fmt(subtotal);
      if (totalEl) totalEl.textContent = fmt(subtotal);
    }
  });
}

// ─────────────────────────────────────────────────────────────
// INTERCEPT PLACE ORDER BUTTON
// ─────────────────────────────────────────────────────────────
function interceptPlaceOrder() {
  const btn = document.getElementById("btnPlaceOrder");
  if (!btn) return;

  btn.removeAttribute("onclick");
  btn.type = "button";

  btn.addEventListener("click", async (e) => {
    e.preventDefault();
    e.stopImmediatePropagation();

    const user      = auth.currentUser;
    const cartItems = window._checkoutCartItems || [];

    if (cartItems.length === 0) {
      showCheckoutAlert("Your cart is empty.", "error");
      return;
    }

    // Sync into checkout.php's scope BEFORE calling validate()
    try { window.cartItems = cartItems; } catch (_) {}

    if (typeof window.validate === "function" && !window.validate()) {
      return;
    }

    const get = id => document.getElementById(id)?.value?.trim() || "";

    const mode        = typeof window.currentMode     !== "undefined" ? window.currentMode     : "ship";
    const payment     = typeof window.selectedPayment !== "undefined" ? window.selectedPayment : "cod";
    const shippingFee = typeof window.shippingFee     !== "undefined" && window.shippingFee !== null ? window.shippingFee : 0;
    const discount    = parseFloat(window.discount || window._appliedDiscount || 0) || 0;

    const orderData = {
      mode,
      payment,
      cartItems,
      shippingFee,
      discount,
      coupon:           get("couponInput"),
      orderNote:        get("orderNote"),
      shippingMethod:   document.querySelector('input[name="shipping_method"]:checked')?.value || "standard",
      guestEmail:       user ? "" : get("guestEmail"),
      selectedStore:    document.querySelector(".store-option.selected")?.querySelector(".store-name")?.textContent || get("selectedStore"),
      selectedBranchId: document.querySelector(".store-option.selected")?.dataset?.branchId || "",
      firstName:  get("fFirst"),
      lastName:   get("fLast"),
      company:    get("fCompany"),
      address:    get("fAddress"),
      apartment:  get("fApartment"),
      city:       get("fCity"),
      province:   get("fProvince"),
      postalCode: get("fPostal"),
      phone:      get("fPhone"),
      country:    get("fCountry") || "Philippines",
    };

    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing Order...';

    try {
      const result = await placeOrder(orderData);
      if (result.success) {
        localStorage.removeItem("pcx_cart");
        localStorage.removeItem("pcx_note");
        localStorage.removeItem("pcx_coupon");
        window.location.href = `order_confirmation.php?order=${encodeURIComponent(result.orderNumber)}`;
      } else {
        showCheckoutAlert(result.message || "Failed to place order.", "error");
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> Place Order';
      }
    } catch (err) {
      console.error("placeOrder:", err);
      showCheckoutAlert("Network error. Please try again.", "error");
      btn.disabled  = false;
      btn.innerHTML = '<i class="fas fa-lock"></i> Place Order';
    }
  });
}

function showCheckoutAlert(msg, type) {
  const el = document.getElementById("checkoutAlert");
  if (!el) { alert(msg); return; }
  el.textContent = msg;
  el.className   = `checkout-alert show ${type}`;
  el.scrollIntoView({ behavior: "smooth", block: "center" });
  setTimeout(() => el.classList.remove("show"), 5500);
}

// ─────────────────────────────────────────────────────────────
// MAIN INIT
// ─────────────────────────────────────────────────────────────
onAuthStateChanged(auth, async user => {
  // Expose to checkout.php's validate() so guest-email check is accurate
  window._firebaseCurrentUser = user || null;

  if (user) {
    const email   = user.email || "";
    const initial = (user.displayName || email || "?")[0].toUpperCase();

    const contactRow     = document.getElementById("contactRow");
    const guestSection   = document.getElementById("guestEmailSection") || document.getElementById("guestEmailRow");
    const contactEmailEl = document.getElementById("contactEmail");
    const userAvatarEl   = document.getElementById("userAvatar");

    if (contactRow)     contactRow.style.display   = "flex";
    if (guestSection)   guestSection.style.display = "none";
    if (contactEmailEl) contactEmailEl.textContent = email;
    if (userAvatarEl)   userAvatarEl.textContent   = initial;

    const [addresses, firestoreCart] = await Promise.all([
      loadFirestoreAddresses(user.uid),
      loadFirestoreCart(user.uid),
    ]);

    // If Firestore has the cart, wipe localStorage to prevent phantom/duplicate items
    let cartItems;
    if (firestoreCart.length > 0) {
      cartItems = firestoreCart;
      try {
        localStorage.removeItem("pcx_cart");
        localStorage.removeItem("pcx_note");
        localStorage.removeItem("pcx_coupon");
      } catch (_) {}
    } else {
      cartItems = loadLocalStorageCart();
    }

    if (addresses.length > 0) {
      const def = addresses.find(a => a.isDefault) || addresses[0];
      prefillShippingForm(def);
      populateAddressSelect(addresses);
    }

    renderCartSummary(cartItems);
    document.getElementById("codNote")?.classList.add("show");

  } else {
    const cartItems = loadLocalStorageCart();
    renderCartSummary(cartItems);
  }

  interceptPlaceOrder();
});