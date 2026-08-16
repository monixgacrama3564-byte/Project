// cart.js
// Replaces: cart.php, cart_handler.php
// Firestore path: carts/{uid}/items/{productId}
// Features: Real-time updates, persistent storage, session-less

import { auth, db } from "./firebase-config.js";
import {
  doc,
  setDoc,
  deleteDoc,
  getDoc,
  getDocs,
  updateDoc,
  collection,
  serverTimestamp,
  onSnapshot,
} from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

// ─────────────────────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────────────────────
function cartItemRef(uid, productId) {
  return doc(db, "carts", uid, "items", String(productId));
}

function cartItemsCol(uid) {
  return collection(db, "carts", uid, "items");
}

async function getCartCount(uid) {
  const snap = await getDocs(cartItemsCol(uid));
  return snap.size;
}

function updateCartBadge(count) {
  document.querySelectorAll("[data-cartcount], .cart-badge, [class*='cart'][class*='count']").forEach((el) => {
    el.textContent = count;
  });
}

function showCartToast(msg) {
  let t = document.getElementById("cartToast");
  if (!t) {
    t = document.createElement("div");
    t.id = "cartToast";
    t.style.cssText = [
      "position:fixed",
      "bottom:28px",
      "left:50%",
      "transform:translateX(-50%) translateY(80px)",
      "background:#222",
      "color:#fff",
      "padding:12px 24px",
      "border-radius:50px",
      "font-size:14px",
      "font-weight:600",
      "z-index:99999",
      "opacity:0",
      "transition:all .35s cubic-bezier(.4,0,.2,1)",
      "white-space:nowrap",
      "pointer-events:none",
    ].join(";");
    document.body.appendChild(t);
  }
  t.textContent = msg;
  requestAnimationFrame(() => {
    t.style.opacity = "1";
    t.style.transform = "translateX(-50%) translateY(0)";
  });
  clearTimeout(window._cartToastTimer);
  window._cartToastTimer = setTimeout(() => {
    t.style.opacity = "0";
    t.style.transform = "translateX(-50%) translateY(80px)";
  }, 2500);
}

// ─────────────────────────────────────────────────────────────
// GET ALL CART ITEMS
// ─────────────────────────────────────────────────────────────
export async function getCartItems() {
  const user = auth.currentUser;
  if (!user) return [];

  try {
    const snap = await getDocs(cartItemsCol(user.uid));
    return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
  } catch (err) {
    console.error("getCartItems error:", err);
    return [];
  }
}

// ─────────────────────────────────────────────────────────────
// ADD TO CART
// qty defaults to 1 if not provided
// ─────────────────────────────────────────────────────────────
export async function addToCart(productData) {
  const user = auth.currentUser;
  if (!user) {
    return { success: false, message: "Please log in first.", redirect: true };
  }

  const {
    id = "",
    name = "Product",
    price = 0,
    image = "",
    variant = "",
    qty = 1,
  } = productData;

  if (!id) return { success: false, message: "Invalid product." };

  const quantity = Math.max(1, Math.min(99, parseInt(qty) || 1));

  try {
    const ref = cartItemRef(user.uid, id);
    const snap = await getDoc(ref);

    if (snap.exists()) {
      // Item already in cart → increment qty
      const existing = snap.data();
      const newQty = Math.min(99, existing.qty + quantity);
      await updateDoc(ref, { qty: newQty, updatedAt: serverTimestamp() });
    } else {
      // Add new item
      await setDoc(ref, {
        productId: id,
        productName: name,
        productPrice: price,
        productImage: image,
        variant: variant || null,
        qty: quantity,
        addedAt: serverTimestamp(),
        updatedAt: serverTimestamp(),
      });
    }

    const count = await getCartCount(user.uid);
    updateCartBadge(count);
    showCartToast(`✓ Added ${name} to cart`);
    return { success: true, message: `Added to cart (${quantity}x)` };

  } catch (err) {
    console.error("addToCart error:", err);
    return { success: false, message: "Could not add to cart." };
  }
}

// ─────────────────────────────────────────────────────────────
// UPDATE CART ITEM QUANTITY
// ─────────────────────────────────────────────────────────────
export async function updateCartQty(productId, qty) {
  const user = auth.currentUser;
  if (!user) return { success: false };

  const quantity = Math.max(1, Math.min(99, parseInt(qty) || 1));

  try {
    await updateDoc(cartItemRef(user.uid, productId), {
      qty: quantity,
      updatedAt: serverTimestamp(),
    });
    return { success: true };
  } catch (err) {
    console.error("updateCartQty error:", err);
    return { success: false };
  }
}

// ─────────────────────────────────────────────────────────────
// REMOVE FROM CART
// ─────────────────────────────────────────────────────────────
export async function removeFromCart(productId) {
  const user = auth.currentUser;
  if (!user) return { success: false };

  try {
    await deleteDoc(cartItemRef(user.uid, productId));
    const count = await getCartCount(user.uid);
    updateCartBadge(count);
    return { success: true };
  } catch (err) {
    console.error("removeFromCart error:", err);
    return { success: false };
  }
}

// ─────────────────────────────────────────────────────────────
// CLEAR ENTIRE CART
// ─────────────────────────────────────────────────────────────
export async function clearCart() {
  const user = auth.currentUser;
  if (!user) return { success: false };

  try {
    const snap = await getDocs(cartItemsCol(user.uid));
    const dels = snap.docs.map((d) => deleteDoc(d.ref));
    await Promise.all(dels);
    updateCartBadge(0);
    return { success: true };
  } catch (err) {
    console.error("clearCart error:", err);
    return { success: false };
  }
}

// ─────────────────────────────────────────────────────────────
// WATCH CART CHANGES IN REAL-TIME
// Useful for live updating cart totals on the page
// callback(items) called whenever cart changes
// Returns unsubscribe function
// ─────────────────────────────────────────────────────────────
export function watchCart(callback) {
  const user = auth.currentUser;
  if (!user) {
    callback([]);
    return () => {};
  }

  return onSnapshot(
    cartItemsCol(user.uid),
    (snap) => {
      const items = snap.docs.map((d) => ({ id: d.id, ...d.data() }));
      callback(items);
      updateCartBadge(items.length);
    },
    (err) => {
      console.error("watchCart error:", err);
      callback([]);
    }
  );
}

// ─────────────────────────────────────────────────────────────
// RENDER CART PAGE
// Displays all cart items with update/delete buttons
// ─────────────────────────────────────────────────────────────
export async function renderCartPage() {
  const container = document.getElementById("cartContainer");
  if (!container) return;

  const user = auth.currentUser;
  if (!user) {
    container.innerHTML =
      '<p style="text-align:center;color:#888;padding:40px 20px;"><a href="#" onclick="window.openPanel(\'login\');return false;" style="color:#003da5;font-weight:600;">Log in</a> to view your cart.</p>';
    return;
  }

  container.innerHTML = '<p style="text-align:center;color:#888;padding:20px;">Loading cart...</p>';

  const items = await getCartItems();

  if (items.length === 0) {
    container.innerHTML =
      '<p style="text-align:center;color:#888;padding:40px 20px;"><i class="fas fa-shopping-cart" style="font-size:32px;display:block;margin-bottom:12px;"></i>Your cart is empty. <a href="home.php" style="color:#003da5;font-weight:600;">Continue shopping</a>.</p>';
    return;
  }

  let subtotal = 0;
  let html = `<div style="max-width:900px;margin:0 auto;">`;

  for (const item of items) {
    const itemTotal = (item.productPrice || 0) * item.qty;
    subtotal += itemTotal;

    html += `
    <div class="cart-item" style="border:1px solid #e0e0e0;border-radius:8px;padding:16px;margin-bottom:12px;background:#fff;display:grid;grid-template-columns:80px 1fr auto;gap:16px;align-items:start;">
      <img src="${item.productImage}" alt="${item.productName}" style="width:80px;height:80px;object-fit:cover;border-radius:4px;background:#f0f0f0;">
      <div>
        <p style="font-size:14px;font-weight:600;color:#222;margin:0 0 4px;">${item.productName}</p>
        ${item.variant ? `<p style="font-size:12px;color:#888;margin:0;">Variant: ${item.variant}</p>` : ""}
        <p style="font-size:13px;color:#888;margin:4px 0;">Price: ₱${(item.productPrice || 0).toLocaleString("en-US", { minimumFractionDigits: 2 })}</p>
      </div>
      <div style="text-align:right;">
        <p style="font-size:15px;font-weight:700;color:#003da5;margin:0 0 8px;">₱${itemTotal.toLocaleString("en-US", { minimumFractionDigits: 2 })}</p>
        <div style="display:flex;gap:4px;align-items:center;justify-content:flex-end;margin-bottom:8px;">
          <button onclick="window.updateQty('${item.id}', ${Math.max(1, item.qty - 1)})" style="width:28px;height:28px;border:1px solid #ddd;background:#fff;cursor:pointer;border-radius:3px;font-weight:600;">−</button>
          <input type="number" id="qty_${item.id}" value="${item.qty}" min="1" max="99" style="width:45px;text-align:center;border:1px solid #ddd;padding:4px;border-radius:3px;font-weight:600;">
          <button onclick="window.updateQty('${item.id}', ${Math.min(99, item.qty + 1)})" style="width:28px;height:28px;border:1px solid #ddd;background:#fff;cursor:pointer;border-radius:3px;font-weight:600;">+</button>
        </div>
        <button onclick="window.removeItem('${item.id}')" style="width:100%;padding:6px;background:#ffebee;color:#cc0000;border:none;border-radius:3px;font-size:12px;font-weight:600;cursor:pointer;">Remove</button>
      </div>
    </div>
    `;
  }

  html += `
    <div style="border-top:2px solid #e0e0e0;padding-top:16px;margin-top:16px;text-align:right;">
      <p style="font-size:15px;color:#888;margin:0 0 8px;">Subtotal: <span style="color:#222;font-weight:600;">₱${subtotal.toLocaleString("en-US", { minimumFractionDigits: 2 })}</span></p>
      <a href="checkout.php" style="display:inline-block;margin-top:16px;padding:14px 32px;background:#003da5;color:#fff;border-radius:6px;font-size:14px;font-weight:700;text-decoration:none;cursor:pointer;">Proceed to Checkout</a>
      <button onclick="window.clearCartBtn()" style="display:block;margin-top:12px;padding:10px 20px;background:#f0f0f0;color:#666;border:none;border-radius:4px;font-size:13px;cursor:pointer;margin-left:auto;margin-right:auto;">Clear Cart</button>
    </div>
    </div>
  `;

  container.innerHTML = html;
}

// Global handlers for cart page
window.updateQty = async (productId, newQty) => {
  const result = await updateCartQty(productId, newQty);
  if (result.success) {
    renderCartPage();
  }
};

window.removeItem = async (productId) => {
  const result = await removeFromCart(productId);
  if (result.success) {
    showCartToast("✓ Removed from cart");
    renderCartPage();
  }
};

window.clearCartBtn = async () => {
  if (!confirm("Clear entire cart?")) return;
  const result = await clearCart();
  if (result.success) {
    showCartToast("✓ Cart cleared");
    renderCartPage();
  }
};

// ─────────────────────────────────────────────────────────────
// ADD TO CART BUTTON HANDLER
// Wire this to product page "Add to Cart" buttons
// ─────────────────────────────────────────────────────────────
export function initAddToCartButtons() {
  document.querySelectorAll(".add-to-cart-btn").forEach((btn) => {
    if (btn.dataset.cartInit) return;
    btn.dataset.cartInit = "1";
    btn.addEventListener("click", async (e) => {
      e.preventDefault();
      if (btn.disabled) return;

      const pid = btn.dataset.productId;
      const pname = btn.dataset.productName;
      const pprice = parseFloat(btn.dataset.productPrice) || 0;
      const pimage = btn.dataset.productImage;
      const variant = btn.dataset.variant || "";

      btn.disabled = true;

      // Check if logged in
      if (!auth.currentUser) {
        if (typeof window.openPanel === "function") {
          window.openPanel("login");
        } else {
          showCartToast("Please log in to add items to cart");
        }
        btn.disabled = false;
        return;
      }

      const result = await addToCart({
        id: pid,
        name: pname,
        price: pprice,
        image: pimage,
        variant: variant,
        qty: 1,
      });

      btn.disabled = false;

      if (result.success) {
        showCartToast(result.message);
      } else if (result.redirect) {
        if (typeof window.openPanel === "function") window.openPanel("login");
      } else {
        showCartToast(result.message);
      }
    });
  });
}

// Update badge on auth state change
auth.onAuthStateChanged(async (user) => {
  if (!user) {
    updateCartBadge(0);
    return;
  }
  try {
    const count = await getCartCount(user.uid);
    updateCartBadge(count);
  } catch (_) {}
});

// ─────────────────────────────────────────────────────────────
// AUTO-INIT
// ─────────────────────────────────────────────────────────────
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initAddToCartButtons);
} else {
  initAddToCartButtons();
}

window.initAddToCartButtons = initAddToCartButtons; // Re-init for dynamic products