// products.js  v2.1
// Firestore path: products/{productId}
// FIX: status field is "In Stock" / "Low Stock" / "Out of Stock" (NOT "Active")
//      Products also need inventoryStatus === "approved" to appear publicly.

import { db } from "./firebase-config.js";
import {
  collection,
  doc,
  getDoc,
  getDocs,
  query,
  where,
  orderBy,
  limit,
  startAfter,
} from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

// ─────────────────────────────────────────────────────────────
// GET SINGLE PRODUCT BY ID
// ─────────────────────────────────────────────────────────────
export async function getProduct(productId) {
  try {
    const snap = await getDoc(doc(db, "products", String(productId)));
    if (!snap.exists()) return null;
    const data = snap.data();
    // Only show approved, non-discontinued products publicly
    if (data.inventoryStatus && data.inventoryStatus !== "approved") return null;
    return { id: snap.id, ...data };
  } catch (err) {
    console.error("getProduct error:", err);
    return null;
  }
}

// ─────────────────────────────────────────────────────────────
// GET ALL PRODUCTS (paginated)
// status values saved by admin: "In Stock", "Low Stock", "Out of Stock", "Discontinued"
// ─────────────────────────────────────────────────────────────
export async function getProducts(options = {}) {
  const {
    category    = null,
    search      = null,
    pageSize    = 20,
    lastVisible = null,
    sortBy      = "createdAt",
    sortOrder   = "desc",
  } = options;

  try {
    // Base: approved products that are not discontinued
    let constraints = [
      where("inventoryStatus", "==", "approved"),
      where("status", "!=", "Discontinued"),
      orderBy("status"),           // required before any other orderBy when using !=
      orderBy(sortBy, sortOrder),
      limit(pageSize + 1),
    ];

    if (category && category !== "all") {
      // category filter — Firestore compound index: inventoryStatus, status, category, createdAt
      constraints = [
        where("inventoryStatus", "==", "approved"),
        where("category", "==", category),
        orderBy(sortBy, sortOrder),
        limit(pageSize + 1),
      ];
    }

    if (lastVisible) constraints.push(startAfter(lastVisible));

    const snap      = await getDocs(query(collection(db, "products"), ...constraints));
    const allDocs   = snap.docs.slice(0, pageSize);
    const hasMore   = snap.docs.length > pageSize;
    const nextCursor = hasMore ? snap.docs[snap.docs.length - 1] : null;

    let products = allDocs.map(d => ({ id: d.id, ...d.data() }));

    // Client-side search (use Algolia for production full-text search)
    if (search) {
      const s = search.toLowerCase();
      products = products.filter(p =>
        (p.name        || "").toLowerCase().includes(s) ||
        (p.brand       || "").toLowerCase().includes(s) ||
        (p.description || "").toLowerCase().includes(s) ||
        (p.sku         || "").toLowerCase().includes(s)
      );
    }

    return { products, hasMore, nextCursor };
  } catch (err) {
    console.error("getProducts error:", err);
    // Fallback: fetch all approved and filter client-side (avoids index issues)
    return getProductsFallback(options);
  }
}

// Fallback: no composite index required
async function getProductsFallback({ category, search, pageSize = 20 } = {}) {
  try {
    const snap = await getDocs(
      query(collection(db, "products"), where("inventoryStatus", "==", "approved"))
    );
    let products = snap.docs
      .map(d => ({ id: d.id, ...d.data() }))
      .filter(p => p.status !== "Discontinued");

    if (category && category !== "all") products = products.filter(p => p.category === category);
    if (search) {
      const s = search.toLowerCase();
      products = products.filter(p =>
        (p.name  || "").toLowerCase().includes(s) ||
        (p.brand || "").toLowerCase().includes(s)
      );
    }

    products.sort((a, b) => (b.createdAt?.seconds || 0) - (a.createdAt?.seconds || 0));
    return { products: products.slice(0, pageSize), hasMore: products.length > pageSize, nextCursor: null };
  } catch (err) {
    console.error("getProductsFallback error:", err);
    return { products: [], hasMore: false, nextCursor: null };
  }
}

// ─────────────────────────────────────────────────────────────
// GET FEATURED PRODUCTS (for homepage)
// ─────────────────────────────────────────────────────────────
export async function getFeaturedProducts(limitCount = 12) {
  try {
    const q    = query(
      collection(db, "products"),
      where("inventoryStatus", "==", "approved"),
      where("isFeatured", "==", true),
      where("status", "in", ["In Stock", "Low Stock"]),
      limit(limitCount)
    );
    const snap = await getDocs(q);
    return snap.docs.map(d => ({ id: d.id, ...d.data() }));
  } catch (err) {
    // Fallback without compound index
    try {
      const snap = await getDocs(
        query(collection(db, "products"), where("isFeatured", "==", true), limit(limitCount * 2))
      );
      return snap.docs
        .map(d => ({ id: d.id, ...d.data() }))
        .filter(p => p.inventoryStatus === "approved" && p.status !== "Discontinued")
        .slice(0, limitCount);
    } catch (e) {
      console.error("getFeaturedProducts error:", e);
      return [];
    }
  }
}

// ─────────────────────────────────────────────────────────────
// GET PRODUCTS BY CATEGORY
// ─────────────────────────────────────────────────────────────
export async function getProductsByCategory(categoryId, limitCount = 20) {
  try {
    const snap = await getDocs(
      query(
        collection(db, "products"),
        where("category", "==", categoryId),
        where("inventoryStatus", "==", "approved"),
        limit(limitCount)
      )
    );
    return snap.docs
      .map(d => ({ id: d.id, ...d.data() }))
      .filter(p => p.status !== "Discontinued");
  } catch (err) {
    console.error("getProductsByCategory error:", err);
    return [];
  }
}

// ─────────────────────────────────────────────────────────────
// GET ALL CATEGORIES
// ─────────────────────────────────────────────────────────────
export async function getCategories() {
  try {
    const snap = await getDocs(collection(db, "categories"));
    if (!snap.empty) return snap.docs.map(d => ({ id: d.id, ...d.data() }));

    // Derive categories from products if no categories collection
    const pSnap  = await getDocs(collection(db, "products"));
    const cats   = [...new Set(pSnap.docs.map(d => d.data().category).filter(Boolean))];
    return cats.map(c => ({ id: c, name: c }));
  } catch (err) {
    console.error("getCategories error:", err);
    return [];
  }
}

// ─────────────────────────────────────────────────────────────
// PRICE FORMATTER
// ─────────────────────────────────────────────────────────────
function fmtPrice(price) {
  return "₱" + parseFloat(price || 0).toLocaleString("en-US", { minimumFractionDigits: 2 });
}

// ─────────────────────────────────────────────────────────────
// RENDER PRODUCT CARD HTML
// ─────────────────────────────────────────────────────────────
function productCardHTML(product) {
  const imageUrl  = product.image || "https://via.placeholder.com/200";
  const inStock   = product.stock > 0;
  const priceStr  = fmtPrice(product.price);
  const stockBadge = !inStock
    ? '<span style="position:absolute;top:8px;right:8px;background:#cc0000;color:#fff;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:700;">Out of Stock</span>'
    : product.status === "Low Stock"
      ? '<span style="position:absolute;top:8px;right:8px;background:#e65100;color:#fff;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:700;">Low Stock</span>'
      : "";

  return `
  <div class="product-card" style="border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;background:#fff;transition:transform .2s,box-shadow .2s;cursor:pointer;" onclick="window.location='product.php?id=${product.id}'" onmouseenter="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(0,0,0,.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
    <div style="width:100%;height:160px;background:#f0f0f0;overflow:hidden;display:flex;align-items:center;justify-content:center;position:relative;">
      <img src="${imageUrl}" alt="${product.name}" style="max-width:100%;max-height:100%;object-fit:contain;" onerror="this.src='https://via.placeholder.com/200'">
      ${stockBadge}
    </div>
    <div style="padding:12px;">
      <p style="font-size:12px;color:#888;margin:0 0 4px;">${product.brand || ""}</p>
      <p style="font-size:13px;font-weight:600;color:#222;margin:0 0 8px;line-height:1.4;min-height:32px;">${product.name}</p>
      <p style="font-size:14px;font-weight:700;color:#003da5;margin:0;">${priceStr}</p>
      <button class="add-to-cart-btn"
              data-product-id="${product.id}"
              data-product-name="${product.name}"
              data-product-price="${product.price}"
              data-product-image="${imageUrl}"
              style="width:100%;margin-top:8px;padding:8px;background:${!inStock ? "#ccc" : "#003da5"};color:#fff;border:none;border-radius:4px;font-size:12px;font-weight:600;cursor:pointer;"
              ${!inStock ? "disabled" : ""}>
        ${!inStock ? "Out of Stock" : "Add to Cart"}
      </button>
    </div>
  </div>`;
}

// ─────────────────────────────────────────────────────────────
// RENDER PRODUCT GRID
// ─────────────────────────────────────────────────────────────
export async function renderProductGrid(containerSelector, options = {}) {
  const container = document.querySelector(containerSelector);
  if (!container) return;

  container.innerHTML = '<p style="text-align:center;color:#888;padding:40px;">Loading products...</p>';

  const { products, hasMore, nextCursor } = await getProducts({
    pageSize: options.pageSize || 20,
    ...options,
  });

  if (products.length === 0) {
    container.innerHTML = '<p style="text-align:center;color:#888;padding:40px;">No products found.</p>';
    return;
  }

  let html = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;">';
  products.forEach(p => { html += productCardHTML(p); });
  html += "</div>";

  if (hasMore) {
    html += `<div style="text-align:center;margin-top:32px;">
      <button id="loadMoreBtn" style="padding:12px 32px;background:#003da5;color:#fff;border:none;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;">Load More Products</button>
    </div>`;
  }

  container.innerHTML = html;

  // Wire load-more
  const lmBtn = container.querySelector("#loadMoreBtn");
  if (lmBtn && nextCursor) {
    lmBtn.addEventListener("click", async () => {
      lmBtn.textContent = "Loading...";
      lmBtn.disabled    = true;
      const more = await getProducts({ ...options, lastVisible: nextCursor });
      const grid = container.querySelector("div");
      more.products.forEach(p => { grid.insertAdjacentHTML("beforeend", productCardHTML(p)); });
      if (!more.hasMore) lmBtn.parentElement?.remove();
      else { lmBtn.textContent = "Load More Products"; lmBtn.disabled = false; }
      if (typeof window.initAddToCartButtons === "function") window.initAddToCartButtons();
      if (typeof window.initWishButtons      === "function") window.initWishButtons();
    });
  }

  // Init buttons for newly rendered products
  if (typeof window.initAddToCartButtons === "function") window.initAddToCartButtons();
  if (typeof window.initWishButtons      === "function") window.initWishButtons();
}

// ─────────────────────────────────────────────────────────────
// RENDER SINGLE PRODUCT DETAIL PAGE
// ─────────────────────────────────────────────────────────────
export async function renderProductDetail(productId, containerSelector) {
  const container = document.querySelector(containerSelector);
  if (!container) return;

  container.innerHTML = '<p style="text-align:center;color:#888;padding:40px;">Loading product...</p>';

  const product = await getProduct(productId);
  if (!product) {
    container.innerHTML = '<p style="text-align:center;color:#cc0000;padding:40px;">Product not found.</p>';
    return;
  }

  const imageUrl    = product.image || "https://via.placeholder.com/400";
  const priceStr    = fmtPrice(product.price);
  const origPrice   = product.originalPrice ? fmtPrice(product.originalPrice) : null;
  const discount    = (origPrice && product.price < product.originalPrice)
    ? Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)
    : 0;
  const inStock     = (product.stock || 0) > 0;
  const maxQty      = Math.min(99, product.stock || 0);

  container.innerHTML = `
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;">
    <div>
      <div style="width:100%;aspect-ratio:1;background:#f0f0f0;border-radius:8px;overflow:hidden;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
        <img src="${imageUrl}" alt="${product.name}" style="max-width:100%;max-height:100%;object-fit:contain;" onerror="this.src='https://via.placeholder.com/400'">
      </div>
      ${discount > 0 ? `<p style="text-align:center;color:#28a745;font-weight:700;">Save ${discount}%</p>` : ""}
    </div>
    <div>
      <p style="font-size:14px;color:#888;margin:0 0 8px;">${product.brand || ""}</p>
      <h1 style="font-size:24px;font-weight:700;color:#222;margin:0 0 16px;line-height:1.3;">${product.name}</h1>
      <div style="margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid #e0e0e0;">
        <p style="font-size:28px;font-weight:700;color:#003da5;margin:0;">${priceStr}</p>
        ${origPrice ? `<p style="font-size:14px;color:#888;text-decoration:line-through;margin:4px 0 0;">Was: ${origPrice}</p>` : ""}
      </div>
      <div style="margin-bottom:20px;">
        <p style="font-size:13px;color:#888;margin:0 0 4px;font-weight:600;">Availability:</p>
        <p style="font-size:14px;color:${inStock ? "#28a745" : "#cc0000"};margin:0;font-weight:700;">
          ${inStock ? product.stock + " in stock" : "Out of Stock"}
        </p>
      </div>
      ${inStock ? `<div style="margin-bottom:20px;">
        <label style="font-size:13px;color:#888;display:block;margin-bottom:8px;font-weight:600;">Quantity:</label>
        <input type="number" id="qtyInput" value="1" min="1" max="${maxQty}"
               style="width:80px;padding:8px;border:1px solid #ddd;border-radius:4px;font-size:14px;font-weight:600;">
      </div>` : ""}
      ${product.description ? `<div style="margin-bottom:24px;padding:16px;background:#f8f8f8;border-radius:6px;">
        <p style="font-size:13px;color:#888;margin:0 0 8px;font-weight:600;">Description:</p>
        <p style="font-size:13px;color:#555;margin:0;line-height:1.6;">${product.description}</p>
      </div>` : ""}
      ${product.specifications && Object.keys(product.specifications).length ? `<div style="margin-bottom:24px;">
        <p style="font-size:13px;color:#888;margin:0 0 12px;font-weight:600;">Specifications:</p>
        <ul style="font-size:13px;color:#555;margin:0;padding-left:20px;line-height:1.8;">
          ${Object.entries(product.specifications).map(([k, v]) => `<li>${k}: ${v}</li>`).join("")}
        </ul>
      </div>` : ""}
      <div style="display:flex;gap:12px;margin-top:32px;">
        <button class="add-to-cart-btn"
                data-product-id="${product.id}"
                data-product-name="${product.name}"
                data-product-price="${product.price}"
                data-product-image="${imageUrl}"
                onclick="window.addItemAndShowMsg('${product.id}','${product.name.replace(/'/g,"\\'")}',${product.price},'${imageUrl}')"
                style="flex:1;padding:14px;background:${!inStock ? "#ccc" : "#003da5"};color:#fff;border:none;border-radius:6px;font-size:14px;font-weight:700;cursor:pointer;"
                ${!inStock ? "disabled" : ""}>
          ${!inStock ? "Out of Stock" : "🛒 Add to Cart"}
        </button>
        <button class="wish-btn"
                data-id="${product.id}"
                data-name="${product.name}"
                data-brand="${product.brand || ""}"
                data-price="${priceStr}"
                data-image="${imageUrl}"
                data-url="product.php?id=${product.id}"
                style="padding:14px 20px;background:#f0f0f0;color:#cc0000;border:none;border-radius:6px;font-size:18px;cursor:pointer;">
          ♡
        </button>
      </div>
    </div>
  </div>`;

  if (typeof window.initWishButtons      === "function") window.initWishButtons();
  if (typeof window.initAddToCartButtons === "function") window.initAddToCartButtons();
}

// Global: add to cart with quantity
window.addItemAndShowMsg = (productId, name, price, image) => {
  const qty = parseInt(document.getElementById("qtyInput")?.value) || 1;
  if (typeof window.addToCart === "function") {
    window.addToCart({ id: productId, name, price, image, qty });
  }
};

// ─────────────────────────────────────────────────────────────
// AUTO-INIT
// ─────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  if (typeof window.initAddToCartButtons === "function") window.initAddToCartButtons();
});

export default {
  getProduct,
  getProducts,
  getFeaturedProducts,
  getProductsByCategory,
  getCategories,
  renderProductGrid,
  renderProductDetail,
};