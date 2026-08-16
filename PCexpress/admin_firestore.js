// admin_firestore.js v5.0
// ══════════════════════════════════════════════════════════════
//  STRICT ROLE-BASED FIRESTORE API — PC Express Multi-Branch

import { auth, db, app } from "./firebase-config.js";
import { initializeApp, deleteApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
import {
  collection, doc, addDoc, setDoc, getDoc, getDocs,
  updateDoc, deleteDoc, query, where, orderBy, limit,
  serverTimestamp, writeBatch, increment, onSnapshot,
} from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";


// ─────────────────────────────────────────────────────────────
//  AUTH & ROLE HELPERS
// ─────────────────────────────────────────────────────────────
function currentUser() { return auth.currentUser; }
function currentUid()  { return auth.currentUser?.uid || null; }

function role() {
  return window.ADMIN_ROLE || sessionStorage.getItem("ADMIN_ROLE") || "customer";
}
function branchId() {
  const b = window.SESSION_BRANCH || sessionStorage.getItem("SESSION_BRANCH");
  return b || null;
}

function isMainAdmin()    { return ["admin", "main_admin"].includes(role()); }
function isSubAdmin()     { return role() === "sub_admin"; }
function isStaff()        { return role() === "staff"; }
function isManager()      { return role() === "manager"; }
function isAnyAdmin()     { return ["admin", "main_admin", "sub_admin", "manager"].includes(role()); }
function isStaffOrAbove() { return ["admin", "main_admin", "sub_admin", "manager", "staff"].includes(role()); }

function canAccessBranch(bid) {
  if (isMainAdmin() || isManager()) return true;
  if (isSubAdmin()) return String(bid) === String(branchId());
  return false;
}
function authFail(msg = "You don't have permission to do that.") {
  return { success: false, message: msg };
}
function ok(data = {}) { return { success: true, ...data }; }


// ─────────────────────────────────────────────────────────────
//  UTILITY HELPERS
// ─────────────────────────────────────────────────────────────
function tsToStr(ts) {
  if (!ts) return null;
  if (ts?.toDate) return ts.toDate().toISOString();
  return String(ts);
}
function docData(snap) {
  if (!snap.exists()) return null;
  return { id: snap.id, ...snap.data() };
}
function snapList(querySnap) {
  return querySnap.docs.map(d => ({ id: d.id, ...d.data() }));
}
function sanitizeStr(val, max = 255) {
  return String(val || "").trim().substring(0, max);
}


// ══════════════════════════════════════════════════════════════
//  OVERVIEW / DASHBOARD STATS
// ══════════════════════════════════════════════════════════════
export async function getOverview() {
  try {
    const bid = isSubAdmin() ? branchId() : null;

    const [ordersSnap, usersSnap, branchesSnap] = await Promise.all([
      getDocs(collection(db, "orders")),
      getDocs(collection(db, "users")),
      getDocs(collection(db, "branches")),
    ]);

    let allOrders = snapList(ordersSnap);
    if (bid) allOrders = allOrders.filter(o => o.branchId === bid);
    const activeOrders = allOrders.filter(o => o.status !== "cancelled");
    const total_sales  = activeOrders.reduce((s, o) => s + (parseFloat(o.grandTotal) || 0), 0);
    const total_orders = allOrders.length;

    const allUsers = snapList(usersSnap);
    const total_users     = allUsers.filter(u => u.role === "customer").length;
    const total_employees = allUsers.filter(u =>
      ["staff", "manager", "sub_admin"].includes(u.role) && (!bid || u.branchId === bid)
    ).length;

    const allBranches    = snapList(branchesSnap);
    const total_branches = allBranches.filter(b => b.status === "active").length;

    let low_stock = 0, out_of_stock = 0, pending_approvals = 0, total_products = 0;

    if (isMainAdmin() || isManager()) {
      const prodsSnap = await getDocs(collection(db, "products"));
      const allProds  = snapList(prodsSnap);
      total_products    = allProds.length;
      low_stock         = allProds.filter(p => (p.stock || 0) > 0 && (p.stock || 0) <= 5).length;
      out_of_stock      = allProds.filter(p => (p.stock || 0) === 0).length;
      pending_approvals = allProds.filter(p => p.inventoryStatus === "pending").length;
    } else if ((isSubAdmin() || isStaff()) && branchId()) {
      const invSnap = await getDocs(
        query(collection(db, "branch_inventory"), where("branchId", "==", branchId()))
      );
      const inv      = snapList(invSnap);
      total_products = inv.length;
      low_stock      = inv.filter(i => (i.quantity || 0) > 0 && (i.quantity || 0) <= (i.reorderLevel || 10)).length;
      out_of_stock   = inv.filter(i => (i.quantity || 0) === 0).length;
    }

    const monthNames = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    const monthMap   = {};
    activeOrders.forEach(o => {
      const d = o.createdAt?.toDate?.() || new Date();
      const key = monthNames[d.getMonth()];
      monthMap[key] = (monthMap[key] || 0) + (parseFloat(o.grandTotal) || 0);
    });
    const monthly = Object.entries(monthMap).map(([month, revenue]) => ({ month, revenue }));

    const status_breakdown = {};
    allOrders.forEach(o => { status_breakdown[o.status] = (status_breakdown[o.status] || 0) + 1; });

    const recent_orders = allOrders
      .sort((a, b) => (b.createdAt?.seconds || 0) - (a.createdAt?.seconds || 0))
      .slice(0, 8)
      .map(o => ({
        id:           o.id,
        order_number: o.orderNumber,
        total_amount: o.grandTotal,
        status:       o.status,
        created_at:   tsToStr(o.createdAt),
        customer:     `${o.shipFirstName || ""} ${o.shipLastName || ""}`.trim() || o.guestEmail || "Guest",
      }));

    let branch_summary = [];
    if (isMainAdmin()) {
      branch_summary = allBranches.slice(0, 6).map(b => ({
        id:      b.id,
        name:    b.name,
        status:  b.status,
        orders:  allOrders.filter(o => o.branchId === b.id).length,
        revenue: allOrders.filter(o => o.branchId === b.id && o.status !== "cancelled")
                          .reduce((s, o) => s + (parseFloat(o.grandTotal) || 0), 0),
      }));
    }

    const pending_orders_count = allOrders.filter(o => o.status === "pending").length;
    const processing_count     = allOrders.filter(o => o.status === "processing").length;

    return ok({
      total_sales, total_orders, total_users, total_branches,
      total_employees, total_products, low_stock, out_of_stock,
      pending_approvals, pending_orders_count, processing_count,
      total_sub_admins: allUsers.filter(u => u.role === "sub_admin").length,
      monthly, status_breakdown, recent_orders, branch_summary,
      invAlerts: low_stock + out_of_stock,
      userRole: role(),
    });
  } catch (e) {
    console.error("getOverview:", e);
    return { success: false, message: e.message };
  }
}


// ══════════════════════════════════════════════════════════════
//  ORDERS  — Staff processes; admins view
// ══════════════════════════════════════════════════════════════
export async function getOrders({ search = "", status = "", branchFilter = "" } = {}) {
  try {
    const snap  = await getDocs(collection(db, "orders"));
    let orders  = snapList(snap);

    if ((isSubAdmin() || isStaff()) && branchId()) {
      orders = orders.filter(o => !o.branchId || o.branchId === branchId());
    }
    if (branchFilter) orders = orders.filter(o => o.branchId === String(branchFilter));
    if (status)       orders = orders.filter(o => o.status === status);
    if (search) {
      const s = search.toLowerCase();
      orders  = orders.filter(o =>
        (o.orderNumber || "").toLowerCase().includes(s) ||
        (o.shipFirstName || "").toLowerCase().includes(s) ||
        (o.guestEmail || "").toLowerCase().includes(s)
      );
    }

    orders = orders
      .sort((a, b) => (b.createdAt?.seconds || 0) - (a.createdAt?.seconds || 0))
      .slice(0, 300)
      .map(o => ({
        ...o,
        // Map Firestore field names to expected display names
        order_number: o.orderNumber || o.order_number || '—',
        customer_name: `${o.shipFirstName || ''} ${o.shipLastName || ''}`.trim() || o.guestEmail || 'Guest',
        customer_email: o.guestEmail || o.email || '',
        payment_method: o.paymentMethod || o.payment_method || '—',
        total_amount: o.grandTotal || o.total_amount || 0,
        items_count: o.items?.length || 0,
        created_at: tsToStr(o.createdAt),
        accepted_by_name: o.acceptedByName || o.accepted_by_name || '',
      }));

    return ok({ orders });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getOrderDetail(orderId) {
  try {
    const orderSnap = await getDoc(doc(db, "orders", orderId));
    if (!orderSnap.exists()) return { success: false, message: "Order not found." };
    const order     = { id: orderSnap.id, ...orderSnap.data(), created_at: tsToStr(orderSnap.data().createdAt) };
    const itemsSnap = await getDocs(collection(db, "orders", orderId, "items"));
    order.items     = snapList(itemsSnap);
    try {
      const logsSnap    = await getDocs(query(collection(db, "orders", orderId, "logs"), orderBy("createdAt", "asc")));
      order.action_logs = snapList(logsSnap).map(l => ({ ...l, created_at: tsToStr(l.createdAt) }));
    } catch (_) { order.action_logs = []; }
    return ok({ order });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getPendingOrders() {
  try {
    const q    = query(collection(db, "orders"), where("status", "in", ["pending", "processing"]));
    const snap = await getDocs(q);
    let orders = snapList(snap);
    if ((isSubAdmin() || isStaff()) && branchId()) {
      orders = orders.filter(o => !o.branchId || o.branchId === branchId());
    }
    return ok({
      orders: orders
        .sort((a, b) => (a.createdAt?.seconds || 0) - (b.createdAt?.seconds || 0))
        .map(o => ({
          ...o,
          // Map Firestore field names to expected display names
          order_number: o.orderNumber || o.order_number || '—',
          customer_name: `${o.shipFirstName || ''} ${o.shipLastName || ''}`.trim() || o.guestEmail || 'Guest',
          customer_email: o.guestEmail || o.email || '',
          items_count: o.items?.length || 0,
          total_amount: o.grandTotal || o.total_amount || 0,
          status: o.status || 'pending',
          accepted_by_name: o.acceptedByName || o.accepted_by_name || '',
          created_at: tsToStr(o.createdAt),
        })),
    });
  } catch (e) { return { success: false, message: e.message }; }
}

async function _staffDisplayName() {
  try {
    const snap = await getDoc(doc(db, "users", currentUid()));
    if (!snap.exists()) return "";
    const d = snap.data();
    return `${d.firstName || ""} ${d.lastName || ""}`.trim();
  } catch (_) { return ""; }
}

async function _updateOrderStatus(orderId, newStatus, note) {
  const ref = doc(db, "orders", orderId);
  const name = await _staffDisplayName();
  await updateDoc(ref, {
    status: newStatus,
    processedBy: currentUid(),
    processed_by: currentUid(),
    processedAt: serverTimestamp(),
    processed_at: serverTimestamp(),
    acceptedBy: currentUid(),
    accepted_by: currentUid(),
    acceptedByName: name,
    accepted_by_name: name,
    updatedAt: serverTimestamp(),
  });
  await addDoc(collection(db, "orders", orderId, "logs"), {
    action: note, performedBy: currentUid(), role: role(), createdAt: serverTimestamp(),
  });
}

// STAFF ONLY: accept order
export async function acceptOrder(orderId) {
  if (!isStaff()) return authFail("Only staff can accept orders.");
  try {
    const snap = await getDoc(doc(db, "orders", orderId));
    if (!snap.exists()) return { success: false, message: "Order not found." };
    if (snap.data().status !== "pending") return { success: false, message: "Only pending orders can be accepted." };
    const name = await _staffDisplayName();
    const updates = {
      status: "processing",
      processedBy: currentUid(),
      processed_by: currentUid(),
      processedAt: serverTimestamp(),
      processed_at: serverTimestamp(),
      acceptedBy: currentUid(),
      accepted_by: currentUid(),
      acceptedByName: name,
      accepted_by_name: name,
      updatedAt: serverTimestamp(),
    };
    if (branchId()) updates.branchId = branchId();
    await updateDoc(doc(db, "orders", orderId), updates);
    await addDoc(collection(db, "orders", orderId, "logs"), {
      action: "Order accepted by staff", performedBy: currentUid(), role: "staff", createdAt: serverTimestamp(),
    });
    return ok({ message: "Order accepted." });
  } catch (e) { return { success: false, message: e.message }; }
}

// STAFF ONLY: advance order status
export async function processOrder(orderId, newStatus) {
  if (!isStaff()) return authFail("Only staff can process orders.");
  const valid = ["processing", "shipped", "out_for_delivery", "completed", "cancelled"];
  if (!valid.includes(newStatus)) return { success: false, message: "Invalid status." };
  try {
    await _updateOrderStatus(orderId, newStatus, `Status → ${newStatus} by staff`);
    return ok({ message: `Order updated to ${newStatus}.` });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function deleteOrder(orderId) {
  if (!isMainAdmin()) return authFail("Only main admin can delete orders.");
  try {
    const [itemsSnap, logsSnap] = await Promise.all([
      getDocs(collection(db, "orders", orderId, "items")),
      getDocs(collection(db, "orders", orderId, "logs")),
    ]);
    const batch = writeBatch(db);
    itemsSnap.docs.forEach(d => batch.delete(d.ref));
    logsSnap.docs.forEach(d => batch.delete(d.ref));
    batch.delete(doc(db, "orders", orderId));
    await batch.commit();
    return ok({ message: "Order deleted." });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getTransactions(params) { return getOrders(params); }


// ══════════════════════════════════════════════════════════════
//  GLOBAL PRODUCT CATALOG  — MAIN ADMIN ONLY
// ══════════════════════════════════════════════════════════════
export async function getProducts({ search = "", category = "", branchFilter = "", invStatus = "" } = {}) {
  try {
    const snap   = await getDocs(collection(db, "products"));
    let products = snapList(snap);

    if (invStatus)    products = products.filter(p => p.inventoryStatus === invStatus);
    if (category)     products = products.filter(p => p.category === category);
    if (branchFilter) products = products.filter(p => p.branchId === String(branchFilter));
    if (search) {
      const s = search.toLowerCase();
      products = products.filter(p =>
        (p.name || "").toLowerCase().includes(s) ||
        (p.sku  || "").toLowerCase().includes(s) ||
        (p.brand || "").toLowerCase().includes(s)
      );
    }

    products = products
      .sort((a, b) => (b.createdAt?.seconds || 0) - (a.createdAt?.seconds || 0))
      .map(p => ({ ...p, created_at: tsToStr(p.createdAt) }));

    return ok({ products });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function saveProduct(fields) {
  if (!isMainAdmin()) {
    if (isSubAdmin()) return authFail("Sub-admins cannot edit the global product catalog. Use Branch Inventory to manage branch stock.");
    if (isStaff())    return authFail("Staff cannot manage products.");
    return authFail();
  }

  let {
    id = null, name = "", sku = "", category = "PC Components",
    price = 0, stock = 0, status = "In Stock",
    description = "", image = "", brand = "", isFeatured = false,
  } = fields;

  name = sanitizeStr(name, 255);
  if (!name || name.length < 2) return { success: false, message: "Product name is required (min 2 chars)." };

  price = parseFloat(price) || 0;
  stock = parseInt(stock)   || 0;
  if (price < 0 || price > 9_999_999) return { success: false, message: "Invalid price." };
  if (stock < 0 || stock > 999_999)   return { success: false, message: "Invalid stock quantity." };

  const validStatuses = ["In Stock", "Low Stock", "Out of Stock", "Discontinued"];
  if (!validStatuses.includes(status)) status = stock === 0 ? "Out of Stock" : "In Stock";
  if (image && !/^https?:\/\//.test(image)) image = "";

  const payload = {
    name,
    sku:             sanitizeStr(sku, 60),
    category:        sanitizeStr(category, 100),
    brand:           sanitizeStr(brand, 100),
    price, stock, status,
    description:     sanitizeStr(description, 5000),
    image:           sanitizeStr(image, 512),
    isFeatured:      !!isFeatured,
    inventoryStatus: "approved",
    updatedAt:       serverTimestamp(),
    updatedBy:       currentUid(),
  };

  try {
    if (id) {
      await updateDoc(doc(db, "products", id), payload);
      return ok({ message: "Product updated.", id });
    } else {
      payload.createdBy = currentUid();
      payload.createdAt = serverTimestamp();
      const ref = await addDoc(collection(db, "products"), payload);
      return ok({ message: "Product added to global catalog.", id: ref.id });
    }
  } catch (e) { return { success: false, message: e.message }; }
}

export async function deleteProduct(productId) {
  if (!isMainAdmin()) return authFail("Only main admin can delete global products.");
  try {
    const invSnap = await getDocs(
      query(collection(db, "branch_inventory"), where("productId", "==", productId))
    );
    const batch = writeBatch(db);
    invSnap.docs.forEach(d => batch.delete(d.ref));
    batch.delete(doc(db, "products", productId));
    await batch.commit();
    return ok({ message: "Product and all branch inventory entries deleted." });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function approveProduct(productId) {
  if (!isMainAdmin()) return authFail("Only main admin can approve products.");
  try {
    await updateDoc(doc(db, "products", productId), {
      inventoryStatus: "approved", approvedBy: currentUid(), approvedAt: serverTimestamp(),
    });
    return ok({ message: "Product approved." });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function rejectProduct(productId) {
  if (!isMainAdmin()) return authFail("Only main admin can reject products.");
  try {
    await updateDoc(doc(db, "products", productId), {
      inventoryStatus: "rejected", rejectedBy: currentUid(), rejectedAt: serverTimestamp(),
    });
    return ok({ message: "Product rejected." });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getPendingApprovals() {
  if (!isMainAdmin()) return authFail("Only main admin can view pending approvals.");
  try {
    const q    = query(collection(db, "products"), where("inventoryStatus", "==", "pending"));
    const snap = await getDocs(q);
    return ok({ products: snapList(snap) });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function restock(productId, qty) {
  if (!isMainAdmin()) return authFail("Only main admin can restock the global catalog.");
  qty = parseInt(qty) || 0;
  if (qty < 1)     return { success: false, message: "Quantity must be at least 1." };
  if (qty > 10000) return { success: false, message: "Cannot restock more than 10,000 at once." };
  try {
    const ref = doc(db, "products", productId);
    await updateDoc(ref, { stock: increment(qty), updatedAt: serverTimestamp() });
    const snap     = await getDoc(ref);
    const newStock = snap.data().stock || 0;
    const st = newStock === 0 ? "Out of Stock" : newStock <= 5 ? "Low Stock" : "In Stock";
    await updateDoc(ref, { status: st });
    return ok({ message: `Restocked! New stock: ${newStock}.` });
  } catch (e) { return { success: false, message: e.message }; }
}


// ══════════════════════════════════════════════════════════════
//  BRANCH INVENTORY
//  sub_admin: own branch | main_admin: all | staff: read-only
// ══════════════════════════════════════════════════════════════
export async function getBranchInventory({ branchFilter = "", search = "" } = {}) {
  if (!isStaffOrAbove()) return authFail("Please log in.");
  
  try {
    let inventory = [];

    // ✅ If MAIN ADMIN: show global products (from "products" collection)
    if (isMainAdmin()) {
  const prodSnap = await getDocs(collection(db, "products"));
  const prods = snapList(prodSnap);
  inventory = prods
    .filter(p => p.inventoryStatus === "approved" && p.status !== "Discontinued")
    .map(p => ({
      id: p.id,
      name: p.name || p.productName || "Unnamed Product",           // ← This field is used
      sku: p.sku || "",
      category: p.category || "",
      quantity: p.stock || 0,
      branch_name: "Global Catalog",
      last_updated: tsToStr(p.updatedAt),
    }));
    } 
    // ✅ If SUB-ADMIN or STAFF: show branch-specific inventory
    else {
      const targetBranch = branchId();
      if (targetBranch) {
        const invQuery = query(collection(db, "branch_inventory"), where("branchId", "==", targetBranch));
        const snap = await getDocs(invQuery);
        const branchSnap = await getDoc(doc(db, "branches", targetBranch));
        const branchName = branchSnap.exists() ? branchSnap.data().name : targetBranch;
        inventory = snapList(snap).map(i => ({
          ...i,
          product_id: i.productId || i.product_id || "",
          product_name: i.productName || i.name || "",
          branch_name: branchName,
          reorder_level: i.reorderLevel || 10,
          unit_cost: i.unitCost || 0,
          last_updated: tsToStr(i.updatedAt),
          quantity: i.quantity || 0,
          inv_status: (i.quantity || 0) === 0 ? "Out of Stock" : (i.quantity || 0) <= (i.reorderLevel || 10) ? "Low Stock" : "active",
        }));
      }
    }

    if (search) {
      const s = search.toLowerCase();
      inventory = inventory.filter(i => 
        (i.name || "").toLowerCase().includes(s) || 
        (i.sku || "").toLowerCase().includes(s)
      );
    }

    inventory.sort((a, b) => (a.quantity || 0) - (b.quantity || 0));
    return ok({ inventory: inventory.slice(0, 500) });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function addBranchInventory(fields) {
  if (isStaff()) return authFail("Staff cannot create inventory listings. Contact your sub-admin.");
  if (!isMainAdmin() && !isSubAdmin()) return authFail();

  let {
    productId = "", product_id = "", productName = "", product_name = "", sku = "",
    quantity  = 0, reorderLevel = 10, reorder_level = 10, unitCost = 0, unit_cost = 0,
    branchId: bid = "", branch_id = "", inventoryStatus = "active",
  } = fields;

  productId = productId || product_id;
  productName = productName || product_name;
  reorderLevel = reorderLevel || reorder_level || 10;
  unitCost = unitCost || unit_cost || 0;
  const targetBranch = isSubAdmin() ? branchId() : sanitizeStr(bid || branch_id, 100);
  if (!targetBranch) return { success: false, message: "Branch ID is required." };
  if (isSubAdmin() && String(targetBranch) !== String(branchId())) {
    return authFail("You can only manage inventory for your assigned branch.");
  }
  if (!productId) return { success: false, message: "Product ID is required." };

  productName  = sanitizeStr(productName, 255);
  quantity     = parseInt(quantity)     || 0;
  reorderLevel = parseInt(reorderLevel) || 10;
  unitCost     = parseFloat(unitCost)   || 0;

  if (quantity < 0 || quantity > 999999) return { success: false, message: "Invalid quantity." };

  const prodSnap = await getDoc(doc(db, "products", productId));
  if (!prodSnap.exists()) {
    return { success: false, message: "Product not found in global catalog. Ask main admin to add it first." };
  }
  const prodData = prodSnap.data();
  if (prodData.inventoryStatus !== "approved") {
    return { success: false, message: "Only approved products can be added to branch inventory." };
  }

  const docId    = `${targetBranch}_${productId}`;
  const existing = await getDoc(doc(db, "branch_inventory", docId));
  if (existing.exists()) {
    return { success: false, message: "This product is already in branch inventory. Use Update Stock instead." };
  }

  try {
    await setDoc(doc(db, "branch_inventory", docId), {
      branchId:        targetBranch,
      productId,
      productName:     productName || prodData.name,
      sku:             sku || prodData.sku || "",
      quantity,
      reorderLevel,
      unitCost,
      inventoryStatus,
      addedBy:         currentUid(),
      updatedAt:       serverTimestamp(),
      createdAt:       serverTimestamp(),
    });

    await addDoc(collection(db, "inventory_transfer_logs"), {
      type:        "add",
      branchId:    targetBranch,
      productId,
      productName: productName || prodData.name,
      quantity,
      performedBy: currentUid(),
      role:        role(),
      createdAt:   serverTimestamp(),
    });

    return ok({ message: "Product added to branch inventory.", id: docId });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function updateInventoryStock(inventoryDocId, quantity, reorderLevel) {
  if (!isStaffOrAbove()) return authFail();
  quantity     = parseInt(quantity)     || 0;
  reorderLevel = parseInt(reorderLevel) || 10;
  if (quantity < 0 || quantity > 999999) return { success: false, message: "Invalid quantity." };

  try {
    const invSnap = await getDoc(doc(db, "branch_inventory", inventoryDocId));
    if (!invSnap.exists()) return { success: false, message: "Inventory record not found." };
    const inv = invSnap.data();

    if (isSubAdmin() && String(inv.branchId) !== String(branchId())) {
      return authFail("You can only update inventory for your own branch.");
    }
    if (isStaff()) {
      if (branchId() && String(inv.branchId) !== String(branchId())) return authFail("Access denied.");
      const userSnap = await getDoc(doc(db, "users", currentUid()));
      if (!userSnap.exists() || userSnap.data().permitStockUpdate !== true) {
        return authFail("Stock updates require explicit permission from your admin.");
      }
    }

    const newStatus = quantity === 0 ? "Out of Stock" : quantity <= reorderLevel ? "Low Stock" : "active";
    await updateDoc(doc(db, "branch_inventory", inventoryDocId), {
      quantity, reorderLevel, inventoryStatus: newStatus,
      updatedAt: serverTimestamp(), updatedBy: currentUid(),
    });
    return ok({ message: `Stock updated to ${quantity}.` });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function deleteBranchInventory(inventoryDocId) {
  if (isStaff()) return authFail("Staff cannot remove inventory listings.");
  if (!isMainAdmin() && !isSubAdmin()) return authFail();

  try {
    const invSnap = await getDoc(doc(db, "branch_inventory", inventoryDocId));
    if (!invSnap.exists()) return { success: false, message: "Inventory record not found." };
    if (isSubAdmin() && String(invSnap.data().branchId) !== String(branchId())) {
      return authFail("You can only delete inventory from your own branch.");
    }
    await deleteDoc(doc(db, "branch_inventory", inventoryDocId));
    return ok({ message: "Inventory entry removed." });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function transferInventory(fields) {
  if (!isMainAdmin() && !isSubAdmin()) {
    return authFail("Only admins can transfer inventory. Staff cannot initiate transfers.");
  }

  let {
    from_branch = "", to_branch = "", from_branch_id = "", to_branch_id = "",
    productId = "", product_id = "", productName = "", product_name = "", quantity = 0, reason = "",
  } = fields;
  from_branch = from_branch || from_branch_id;
  to_branch = to_branch || to_branch_id;
  productId = productId || product_id;
  productName = productName || product_name;
  quantity = parseInt(quantity) || 0;

  if (!from_branch || !to_branch)  return { success: false, message: "Source and destination branches are required." };
  if (from_branch === to_branch)   return { success: false, message: "Source and destination cannot be the same branch." };
  if (!productId)                  return { success: false, message: "Product is required." };
  if (quantity < 1)                return { success: false, message: "Quantity must be at least 1." };
  if (isSubAdmin() && String(from_branch) !== String(branchId())) {
    return authFail("You can only transfer inventory from your own branch.");
  }

  const srcDocId = `${from_branch}_${productId}`;
  const dstDocId = `${to_branch}_${productId}`;

  try {
    const srcSnap = await getDoc(doc(db, "branch_inventory", srcDocId));
    if (!srcSnap.exists()) return { success: false, message: "Source inventory not found." };
    const srcQty  = srcSnap.data().quantity || 0;
    if (srcQty < quantity) return { success: false, message: `Insufficient stock. Available: ${srcQty}.` };

    const batch = writeBatch(db);

    batch.update(doc(db, "branch_inventory", srcDocId), {
      quantity:  srcQty - quantity,
      updatedAt: serverTimestamp(), updatedBy: currentUid(),
    });

    const dstSnap = await getDoc(doc(db, "branch_inventory", dstDocId));
    if (dstSnap.exists()) {
      batch.update(doc(db, "branch_inventory", dstDocId), {
        quantity:  (dstSnap.data().quantity || 0) + quantity,
        updatedAt: serverTimestamp(), updatedBy: currentUid(),
      });
    } else {
      batch.set(doc(db, "branch_inventory", dstDocId), {
        branchId:        to_branch,
        productId,
        productName:     productName || srcSnap.data().productName || "",
        sku:             srcSnap.data().sku || "",
        quantity,
        reorderLevel:    srcSnap.data().reorderLevel || 10,
        unitCost:        srcSnap.data().unitCost || 0,
        inventoryStatus: "active",
        addedBy:         currentUid(),
        updatedAt:       serverTimestamp(),
        createdAt:       serverTimestamp(),
      });
    }

    const logRef = doc(collection(db, "inventory_transfer_logs"));
    batch.set(logRef, {
      type:          "transfer",
      fromBranch:    from_branch,
      toBranch:      to_branch,
      from_branch,
      to_branch,
      productId,
      productName:   productName || srcSnap.data().productName || "",
      product_name:  productName || srcSnap.data().productName || "",
      quantity,
      reason:        sanitizeStr(reason, 500),
      transferredBy: currentUid(),
      role:          role(),
      status:        "completed",
      createdAt:     serverTimestamp(),
    });

    await batch.commit();
    return ok({ message: `${quantity} units transferred successfully.` });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getTransferLogs(branchFilter = "") {
  if (!isStaffOrAbove()) return authFail();
  try {
    const snap = await getDocs(
      query(collection(db, "inventory_transfer_logs"), orderBy("createdAt", "desc"))
    );
    let logs = snapList(snap).map(l => ({
      ...l,
      from_branch: l.fromBranch || l.from_branch || "—",
      to_branch: l.toBranch || l.to_branch || "—",
      product_name: l.productName || l.product_name || "",
      created_at: tsToStr(l.createdAt),
    }));
    if (isSubAdmin() && branchId()) {
      logs = logs.filter(l => l.fromBranch === branchId() || l.toBranch === branchId() || l.branchId === branchId());
    } else if (branchFilter) {
      logs = logs.filter(l => l.fromBranch === branchFilter || l.toBranch === branchFilter || l.branchId === branchFilter);
    }
    return ok({ logs: logs.slice(0, 200) });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getInventory(params) { return getBranchInventory(params); }


// ══════════════════════════════════════════════════════════════
//  BRANCHES  (main_admin only)
// ══════════════════════════════════════════════════════════════
export async function getBranches() {
  try {
    const snap     = await getDocs(collection(db, "branches"));
    const branches = snapList(snap);
    const [staffSnap, ordersSnap, invSnap] = await Promise.all([
      getDocs(collection(db, "branch_staff")),
      getDocs(collection(db, "orders")),
      getDocs(collection(db, "branch_inventory")),
    ]);
    const allStaff  = snapList(staffSnap);
    const allOrders = snapList(ordersSnap);
    const allInv    = snapList(invSnap);

    return ok({
      branches: branches
        .sort((a, b) => (b.isMain ? 1 : 0) - (a.isMain ? 1 : 0))
        .map(b => ({
          ...b,
          staff_count:        allStaff.filter(s => s.branchId === b.id).length,
          order_count:        allOrders.filter(o => o.branchId === b.id).length,
          total_revenue:      allOrders.filter(o => o.branchId === b.id && o.status !== "cancelled")
                                       .reduce((s, o) => s + (parseFloat(o.grandTotal) || 0), 0),
          total_stock:        allInv.filter(i => i.branchId === b.id).reduce((s, i) => s + (i.quantity || 0), 0),
          low_stock_count:    allInv.filter(i => i.branchId === b.id && i.quantity > 0 && i.quantity <= (i.reorderLevel || 10)).length,
          out_of_stock_count: allInv.filter(i => i.branchId === b.id && i.quantity === 0).length,
          created_at:         tsToStr(b.createdAt),
        })),
    });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getBranchesSimple() {
  try {
    const snap = await getDocs(collection(db, "branches"));
    return ok({
      branches: snapList(snap)
        .filter(b => b.status !== "inactive")
        .map(b => ({ id: b.id, name: b.name, isMain: b.isMain || false, city: b.city || "" })),
    });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getBranchDetail(branchDocId) {
  if (isSubAdmin() && String(branchId()) !== branchDocId) return authFail("Access denied.");
  try {
    const snap = await getDoc(doc(db, "branches", branchDocId));
    if (!snap.exists()) return { success: false, message: "Branch not found." };
    const data = snap.data();
    const branch = {
      id: snap.id, ...data,
      postal_code: data.postalCode || data.postal_code || "",
    };
    const staffSnap = await getDocs(query(collection(db, "branch_staff"), where("branchId", "==", branchDocId)));
    branch.staff = snapList(staffSnap);
    return ok({ branch });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function addBranch(fields) {
  if (!isMainAdmin()) return authFail("Only main admin can add branches.");
  let { name = "", address = "", city = "", province = "", postalCode = "", phone = "", email = "" } = fields;
  name = sanitizeStr(name, 120);
  if (!name || name.length < 3) return { success: false, message: "Branch name must be at least 3 characters." };
  if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) return { success: false, message: "Invalid email address." };
  try {
    const q = query(collection(db, "branches"), where("name", "==", name));
    if (!(await getDocs(q)).empty) return { success: false, message: "A branch with that name already exists." };
    const ref = await addDoc(collection(db, "branches"), {
      name, address: sanitizeStr(address, 255), city: sanitizeStr(city, 100),
      province: sanitizeStr(province, 100), postalCode: sanitizeStr(postalCode, 20),
      phone: sanitizeStr(phone, 40), email: sanitizeStr(email, 180),
      status: "active", isMain: false, subAdminId: null,
      createdAt: serverTimestamp(), updatedAt: serverTimestamp(), createdBy: currentUid(),
    });
    return ok({ message: "Branch created successfully.", id: ref.id });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function updateBranch(branchDocId, fields) {
  if (!isMainAdmin()) return authFail("Only main admin can update branches.");
  let { name = "", address = "", city = "", province = "", postalCode = "", phone = "", email = "", status = "active" } = fields;
  name = sanitizeStr(name, 120);
  if (!branchDocId || !name) return { success: false, message: "Branch ID and name are required." };
  if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) return { success: false, message: "Invalid email address." };
  if (!["active", "inactive"].includes(status)) status = "active";
  try {
    await updateDoc(doc(db, "branches", branchDocId), {
      name, address, city, province, postalCode, phone, email, status, updatedAt: serverTimestamp(),
    });
    return ok({ message: "Branch updated." });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function deleteBranch(branchDocId) {
  if (!isMainAdmin()) return authFail("Only main admin can delete branches.");
  try {
    const snap = await getDoc(doc(db, "branches", branchDocId));
    if (!snap.exists()) return { success: false, message: "Branch not found." };
    if (snap.data().isMain) return { success: false, message: "Cannot delete the main branch." };
    const batch = writeBatch(db);
    const [staffSnap, invSnap, usersSnap] = await Promise.all([
      getDocs(query(collection(db, "branch_staff"),     where("branchId", "==", branchDocId))),
      getDocs(query(collection(db, "branch_inventory"),  where("branchId", "==", branchDocId))),
      getDocs(query(collection(db, "users"),             where("branchId", "==", branchDocId))),
    ]);
    staffSnap.docs.forEach(d => batch.delete(d.ref));
    invSnap.docs.forEach(d => batch.delete(d.ref));
    usersSnap.docs.forEach(d => batch.update(d.ref, { branchId: null }));
    batch.delete(doc(db, "branches", branchDocId));
    await batch.commit();
    return ok({ message: "Branch deleted." });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function setMainBranch(branchDocId) {
  if (!isMainAdmin()) return authFail("Only main admin can set the main branch.");
  try {
    const snap  = await getDocs(collection(db, "branches"));
    const batch = writeBatch(db);
    snap.docs.forEach(d => batch.update(d.ref, { isMain: d.id === branchDocId }));
    await batch.commit();
    return ok({ message: "Main branch updated." });
  } catch (e) { return { success: false, message: e.message }; }
}


// ══════════════════════════════════════════════════════════════
//  USER / EMPLOYEE MANAGEMENT
// ══════════════════════════════════════════════════════════════
export async function getUsers({ search = "", status = "", filterRole = "", branchFilter = "" } = {}) {
  if (!isStaffOrAbove()) return authFail();
  try {
    const snap  = await getDocs(collection(db, "users"));
    let users   = snapList(snap);

    if (isSubAdmin() && branchId()) {
      users = users.filter(u => u.branchId === branchId() || u.id === currentUid());
    } else if (isStaff() && branchId()) {
      users = users.filter(u => u.branchId === branchId());
    }
    if (filterRole)   users = users.filter(u => u.role === filterRole);
    if (status)       users = users.filter(u => (u.status || "active") === status);
    if (branchFilter) users = users.filter(u => u.branchId === branchFilter);
    if (search) {
      const s = search.toLowerCase();
      users = users.filter(u =>
        (u.firstName || "").toLowerCase().includes(s) ||
        (u.lastName  || "").toLowerCase().includes(s) ||
        (u.email     || "").toLowerCase().includes(s)
      );
    }

    return ok({
      users: users
        .sort((a, b) => (b.createdAt?.seconds || 0) - (a.createdAt?.seconds || 0))
        .slice(0, 500)
        .map(u => ({
          ...u,
          first_name: u.firstName,
          last_name: u.lastName,
          branch_id: u.branchId,
          created_at: tsToStr(u.createdAt),
        })),
    });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getUsersSimple(branchFilter = "") {
  try {
    const snap  = await getDocs(collection(db, "users"));
    let users   = snapList(snap).filter(u => u.status !== "inactive");
    if (branchFilter) users = users.filter(u => u.branchId === branchFilter);
    if (isSubAdmin() && branchId()) users = users.filter(u => u.branchId === branchId());
    return ok({
  users: users
    .sort((a, b) => (b.createdAt?.seconds || 0) - (a.createdAt?.seconds || 0))
    .slice(0, 500)
    .map(u => ({ 
      ...u, 
      created_at: tsToStr(u.createdAt),
  first_name: u.firstName,    // ← Add both variants for compatibility
  last_name: u.lastName,
  firstName: u.firstName,
  lastName: u.lastName
    })),
});
  } catch (e) { return { success: false, message: e.message }; }
}

export async function addUser(fields) {
  if (!isAnyAdmin()) return authFail();
  let {
    firstName = "", first_name = "", lastName = "", last_name = "", email = "", status = "active",
    role: newRole = "customer", branchId: bid = null, branch_id = null,
    employeeType = "", employee_type = "", position = "", department = "",
    password = "",
  } = fields;

  firstName = sanitizeStr(firstName || first_name, 80);
  lastName  = sanitizeStr(lastName  || last_name,  80);
  bid = bid || branch_id || null;
  employeeType = employeeType || employee_type || "";

  email = sanitizeStr(email, 180).toLowerCase();

  if (!firstName || !lastName || !email) return { success: false, message: "First name, last name, and email are required." };
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) return { success: false, message: "Invalid email address." };

  const validRoles = ["customer", "staff", "manager", "sub_admin"];
  if (isMainAdmin()) validRoles.push("main_admin", "admin");
  if (!validRoles.includes(newRole)) newRole = "customer";

  // Sub-admin can ONLY add staff to their own branch
  if (isSubAdmin()) {
    bid     = branchId();
    newRole = "staff";
    if (!bid) return { success: false, message: "Your account has no branch assigned." };
  }

  if (!bid && ["staff", "manager", "sub_admin"].includes(newRole)) {
    return { success: false, message: "Please select a branch for this employee." };
  }

  const existing = await getDocs(query(collection(db, "users"), where("email", "==", email)));
  if (!existing.empty) return { success: false, message: "Email already registered." };

  if (newRole === "sub_admin" && bid) {
    const saSnap = await getDocs(query(collection(db, "users"), where("branchId", "==", bid), where("role", "==", "sub_admin")));
    if (!saSnap.empty) return { success: false, message: "This branch already has a Sub-Admin." };
  }

  try {
    let userDocId = null;

    if (password && ["employee", "staff", "manager", "sub_admin", "main_admin", "admin"].includes(newRole)) {
      const { getAuth, createUserWithEmailAndPassword, updateProfile, signOut } =
        await import("https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js");
      const secondaryApp = initializeApp(app.options, `Secondary_${Date.now()}`);
      const secondaryAuth = getAuth(secondaryApp);
      try {
        const cred = await createUserWithEmailAndPassword(secondaryAuth, email, password);
        await updateProfile(cred.user, { displayName: `${firstName} ${lastName}` });
        userDocId = cred.user.uid;
        await signOut(secondaryAuth);
      } finally {
        await deleteApp(secondaryApp);
      }
      await setDoc(doc(db, "users", userDocId), {
        firstName, lastName, email, status, role: newRole,
        branchId: bid || null, employeeType, position, department,
        permitStockUpdate: false,
        authUid: userDocId,
        assignedBy: currentUid(),
        createdAt: serverTimestamp(), updatedAt: serverTimestamp(),
      });
    } else {
      const ref = await addDoc(collection(db, "users"), {
        firstName, lastName, email, status, role: newRole,
        branchId: bid || null, employeeType, position, department,
        permitStockUpdate: false,
        assignedBy: currentUid(),
        createdAt: serverTimestamp(), updatedAt: serverTimestamp(),
      });
      userDocId = ref.id;
    }

    if (bid && ["staff", "manager", "sub_admin"].includes(newRole)) {
      await setDoc(doc(db, "branch_staff", `${bid}_${userDocId}`), {
        branchId: bid,
        userId: userDocId,
        role: newRole,
        position,
        department,
        assignedBy: currentUid(),
        createdAt: serverTimestamp(),
      });
    }

    if (newRole === "sub_admin" && bid) {
      await updateDoc(doc(db, "branches", bid), { subAdminId: userDocId, updatedAt: serverTimestamp() });
    }

    return ok({ message: "User created.", id: userDocId });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function updateUser(userId, fields) {
  if (!isAnyAdmin()) return authFail();
  let {
    firstName = "", first_name = "", lastName = "", last_name = "",
    role: newRole = "customer", status = "active",
    branchId: bid = null, branch_id = null,
    employeeType = "", employee_type = "", permitStockUpdate = false,
  } = fields;
  firstName = firstName || first_name;
  lastName = lastName || last_name;
  bid = bid || branch_id || null;
  employeeType = employeeType || employee_type || "";
  userId = String(userId || "");
  if (!userId) return { success: false, message: "Invalid user ID." };
  if (!["active", "inactive"].includes(status)) status = "active";
  if (isSubAdmin()) { newRole = "staff"; bid = branchId(); }
  try {
    await updateDoc(doc(db, "users", userId), {
      firstName, lastName, role: newRole, status, branchId: bid || null,
      employeeType, permitStockUpdate: !!permitStockUpdate,
      updatedAt: serverTimestamp(),
    });
    if (newRole === "sub_admin" && bid) {
      const prev  = await getDocs(query(collection(db, "users"), where("branchId", "==", bid), where("role", "==", "sub_admin")));
      const batch = writeBatch(db);
      prev.docs.forEach(d => { if (d.id !== userId) batch.update(d.ref, { role: "staff" }); });
      batch.update(doc(db, "branches", bid), { subAdminId: userId });
      await batch.commit();
    }
    return ok({ message: "User updated." });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function deleteUser(userId) {
  if (!isMainAdmin()) return authFail("Only main admin can delete users.");
  if (userId === currentUid()) return { success: false, message: "Cannot delete yourself." };
  try {
    const batch     = writeBatch(db);
    const staffSnap = await getDocs(query(collection(db, "branch_staff"), where("userId", "==", userId)));
    staffSnap.docs.forEach(d => batch.delete(d.ref));
    batch.delete(doc(db, "users", userId));
    await batch.commit();
    return ok({ message: "User deleted." });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function toggleUserStatus(userId, newStatus) {
  if (!isAnyAdmin()) return authFail();
  if (!["active", "inactive"].includes(newStatus)) return { success: false, message: "Invalid status." };
  try {
    await updateDoc(doc(db, "users", userId), { status: newStatus, updatedAt: serverTimestamp() });
    return ok({ message: `User ${newStatus}.` });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function assignSubAdmin(userId, branchDocId) {
  if (!isMainAdmin()) return authFail("Only main admin can assign sub-admins.");
  try {
    const prev  = await getDocs(query(collection(db, "users"), where("branchId", "==", branchDocId), where("role", "==", "sub_admin")));
    const batch = writeBatch(db);
    prev.docs.forEach(d => { if (d.id !== userId) batch.update(d.ref, { role: "staff" }); });
    batch.update(doc(db, "users", userId), { role: "sub_admin", branchId: branchDocId, updatedAt: serverTimestamp() });
    batch.update(doc(db, "branches", branchDocId), { subAdminId: userId });
    await batch.commit();
    return ok({ message: "Sub-admin assigned." });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function assignEmployeeBranch(userId, branchDocId, position = "", department = "") {
  if (!isMainAdmin() && !isSubAdmin()) return authFail();
  if (isSubAdmin() && String(branchDocId) !== String(branchId())) return authFail("You can only assign staff to your branch.");
  try {
    await updateDoc(doc(db, "users", userId), { branchId: branchDocId, position, department, updatedAt: serverTimestamp() });
    await setDoc(doc(db, "branch_staff", `${branchDocId}_${userId}`), {
      branchId: branchDocId, userId, position, department,
      assignedBy: currentUid(), assignedAt: serverTimestamp(),
    });
    return ok({ message: "Employee assigned." });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function removeBranchStaff(userId, branchDocId) {
  if (!isMainAdmin() && !isSubAdmin()) return authFail();
  if (isSubAdmin() && String(branchDocId) !== String(branchId())) return authFail();
  try {
    await deleteDoc(doc(db, "branch_staff", `${branchDocId}_${userId}`));
    await updateDoc(doc(db, "users", userId), { branchId: null, updatedAt: serverTimestamp() });
    return ok({ message: "Staff removed from branch." });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getSubAdmins() {
  if (!isMainAdmin()) return authFail();
  try {
    const [usersSnap, branchesSnap] = await Promise.all([
      getDocs(query(collection(db, "users"), where("role", "==", "sub_admin"))),
      getDocs(collection(db, "branches")),
    ]);
    const branchMap = {};
    snapList(branchesSnap).forEach(b => { branchMap[b.id] = b.name; });
    const sub_admins = snapList(usersSnap).map(u => ({
      ...u,
      first_name: u.firstName,
      last_name: u.lastName,
      branch_name: u.branchId ? (branchMap[u.branchId] || u.branchId) : null,
      created_at: tsToStr(u.createdAt),
    }));
    return ok({ sub_admins });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getEmployees({ branchFilter = "", roleFilter = "" } = {}) {
  if (!isStaffOrAbove()) return authFail();
  try {
    const snap    = await getDocs(collection(db, "users"));
    // Include 'employee' as a legacy alias alongside 'staff'; primary roles are staff/manager/sub_admin
    let employees = snapList(snap).filter(u =>
      ["staff", "employee", "manager", "sub_admin"].includes(u.role)
    );
    if (isSubAdmin() && branchId()) employees = employees.filter(u => u.branchId === branchId());
    if (branchFilter) employees = employees.filter(u => u.branchId === branchFilter);
    // When filtering by 'staff', also include legacy 'employee' roles
    if (roleFilter === 'staff') {
      employees = employees.filter(u => u.role === 'staff' || u.role === 'employee');
    } else if (roleFilter) {
      employees = employees.filter(u => u.role === roleFilter);
    }
    const branchesSnap = await getDocs(collection(db, "branches"));
    const branchMap = {};
    snapList(branchesSnap).forEach(b => { branchMap[b.id] = b.name; });
    employees = employees.map(e => ({
      ...e,
      first_name: e.firstName,
      last_name: e.lastName,
      branch_name: e.branchId ? (branchMap[e.branchId] || "") : "",
    }));
    return ok({ employees: employees.slice(0, 200) });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getBranchStaff(branchDocId) {
  if (isSubAdmin() && String(branchDocId) !== String(branchId())) return authFail("Access denied.");
  try {
    const snap = await getDocs(query(collection(db, "branch_staff"), where("branchId", "==", branchDocId)));
    const staffRecords = snapList(snap);
    const bSnap = await getDoc(doc(db, "branches", branchDocId));
    const bName = bSnap.exists() ? bSnap.data().name : branchDocId;

    const staffWithDetails = await Promise.all(
      staffRecords.map(async (sr) => {
        const userSnap = await getDoc(doc(db, "users", sr.userId));
        const userData = userSnap.exists() ? userSnap.data() : {};
        return {
          ...sr,
          user_id: sr.userId,
          branch_id: sr.branchId || branchDocId,
          name: `${userData.firstName || ''} ${userData.lastName || ''}`.trim() || userData.email || 'Unknown',
          email: userData.email || '—',
          role: sr.role,
          branch_name: bName,
          position: sr.position || '—',
          department: sr.department || '—',
          assigned_at: tsToStr(sr.assignedAt || sr.createdAt),
        };
      })
    );

    return ok({ staff: staffWithDetails });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getProductsSimple() {
  try {
    const snap  = await getDocs(collection(db, "products"));
    const prods = snapList(snap)
      .filter(p => p.inventoryStatus === "approved" && p.status !== "Discontinued")
      .sort((a, b) => (a.name || "").localeCompare(b.name || ""));
    return ok({ products: prods.slice(0, 300).map(p => ({ id: p.id, name: p.name, sku: p.sku, price: p.price, stock: p.stock, category: p.category })) });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function inventoryLookup(searchTerm = "") {
  const bid = (isSubAdmin() || isStaff()) ? branchId() : null;
  const s   = (searchTerm || "").toLowerCase();
  try {
    const snap = await getDocs(collection(db, "products"));
    let prods  = snapList(snap).filter(p =>
      (p.name || "").toLowerCase().includes(s) ||
      (p.sku  || "").toLowerCase().includes(s)
    );
    if (bid) {
      const invSnap = await getDocs(query(collection(db, "branch_inventory"), where("branchId", "==", bid)));
      const invMap  = {};
      invSnap.docs.forEach(d => { invMap[d.data().productId] = d.data().quantity || 0; });
      prods = prods.map(p => ({ ...p, branch_stock: invMap[p.id] ?? 0 }));
    }
    return ok({ products: prods.slice(0, 20) });
  } catch (e) { return { success: false, message: e.message }; }
}


// ══════════════════════════════════════════════════════════════
//  TASKS
// ══════════════════════════════════════════════════════════════
export async function getTasks({ branchFilter = "" } = {}) {
  if (!isStaffOrAbove()) return authFail();
  try {
    const snap  = await getDocs(collection(db, "tasks"));
    let tasks   = snapList(snap);
    const bid   = (isSubAdmin() || isStaff()) ? branchId() : branchFilter;
    if (bid) tasks = tasks.filter(t => t.branchId === bid);
    if (isStaff()) tasks = tasks.filter(t => t.assignedTo === currentUid() || t.assignedTo == null);

    const usersSnap = await getDocs(collection(db, "users"));
    const userMap = {};
    snapList(usersSnap).forEach(u => {
      userMap[u.id] = `${u.firstName || ""} ${u.lastName || ""}`.trim() || u.email;
    });
    const branchesSnap = await getDocs(collection(db, "branches"));
    const branchMap = {};
    snapList(branchesSnap).forEach(b => { branchMap[b.id] = b.name; });

    tasks = tasks.map(t => ({
      ...t,
      assigned_to_name: userMap[t.assignedTo] || "",
      branch_name: t.branchId ? (branchMap[t.branchId] || "") : "",
      due_date: t.dueDate || t.due_date || null,
    }));

    return ok({ tasks: tasks.sort((a, b) => (a.dueDate || a.due_date || "").localeCompare(b.dueDate || b.due_date || "")) });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function addTask(fields) {
  if (!isMainAdmin() && !isSubAdmin()) return authFail("Only admins can assign tasks.");
  let {
    branchId: bid = "", branch_id = "", employeeId = "", assignedTo = "", assigned_to = "",
    taskType = "", task_type = "", title = "", description = "",
    priority = "medium", dueDate = null, due_date = null,
  } = fields;
  bid = isSubAdmin() ? branchId() : (bid || branch_id || "");
  const assignee = assignedTo || assigned_to || employeeId || null;
  taskType = taskType || task_type || "general";
  dueDate = dueDate || due_date || null;
  if (!title) return { success: false, message: "Task title is required." };
  if (!bid) return { success: false, message: "Branch is required." };
  if (!assignee) return { success: false, message: "Assign an employee." };
  try {
    const ref = await addDoc(collection(db, "tasks"), {
      branchId: bid,
      assignedTo: assignee,
      taskType: sanitizeStr(taskType, 80),
      title: sanitizeStr(title, 255),
      description: sanitizeStr(description, 5000),
      priority: sanitizeStr(priority, 20),
      dueDate: dueDate || null,
      status: "pending",
      assignedBy: currentUid(),
      createdAt: serverTimestamp(),
      updatedAt: serverTimestamp(),
    });
    return ok({ message: "Task assigned.", id: ref.id });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function updateTaskStatus(taskId, newStatus) {
  if (!isStaffOrAbove()) return authFail();
  if (!["pending", "in_progress", "completed", "cancelled"].includes(newStatus)) {
    return { success: false, message: "Invalid status." };
  }
  try {
    const payload = {
      status: newStatus,
      updatedAt: serverTimestamp(),
      updatedBy: currentUid(),
    };
    if (newStatus === "completed") {
      payload.completedAt = serverTimestamp();
      payload.completedBy = currentUid();
    }
    await updateDoc(doc(db, "tasks", taskId), payload);
    return ok({ message: "Task updated." });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function deleteTask(taskId) {
  if (!isMainAdmin() && !isSubAdmin()) return authFail();
  try {
    await deleteDoc(doc(db, "tasks", taskId));
    return ok({ message: "Task deleted." });
  } catch (e) { return { success: false, message: e.message }; }
}


// ══════════════════════════════════════════════════════════════
//  ANALYTICS
// ══════════════════════════════════════════════════════════════
export async function getAnalytics() {
  if (!isStaffOrAbove()) return authFail();
  try {
    const bid        = (isSubAdmin() || isStaff()) ? branchId() : null;
    const ordersSnap = await getDocs(collection(db, "orders"));
    let orders       = snapList(ordersSnap);
    if (bid) orders  = orders.filter(o => o.branchId === bid);

    const total_revenue = orders.filter(o => o.status !== "cancelled").reduce((s, o) => s + (parseFloat(o.grandTotal) || 0), 0);
    const total_orders  = orders.length;
    const status_breakdown = {};
    orders.forEach(o => { status_breakdown[o.status] = (status_breakdown[o.status] || 0) + 1; });

    const monthNames = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    const monthMap   = {};
    orders.filter(o => o.status !== "cancelled").forEach(o => {
      const d = o.createdAt?.toDate?.() || new Date();
      const key = monthNames[d.getMonth()];
      monthMap[key] = (monthMap[key] || 0) + (parseFloat(o.grandTotal) || 0);
    });

    let top_products = [];
    if (isMainAdmin()) {
      const itemsMap = {};
      for (const order of orders.slice(0, 100)) {
        try {
          const iSnap = await getDocs(collection(db, "orders", order.id, "items"));
          iSnap.docs.forEach(d => {
            const it = d.data();
            const key = it.productId || it.productName;
            if (!itemsMap[key]) itemsMap[key] = { name: it.productName, qty: 0, revenue: 0 };
            itemsMap[key].qty     += (it.qty || 0);
            itemsMap[key].revenue += (it.lineTotal || 0);
          });
        } catch (_) {}
      }
      top_products = Object.values(itemsMap)
        .sort((a, b) => b.revenue - a.revenue)
        .slice(0, 10);
    }

    return ok({
      total_revenue, total_orders, status_breakdown, top_products,
      monthly: Object.entries(monthMap).map(([month, revenue]) => ({ month, revenue })),
    });
  } catch (e) { return { success: false, message: e.message }; }
}

export async function getEmployeeDashboard() {
  const uid = currentUid();
  const bid = branchId();
  
  if (!uid) return { success: false, message: "Not authenticated." };
  
  try {
    // ✅ Get all tasks assigned to this user (not just incomplete)
    const tasksSnap = await getDocs(
      query(collection(db, "tasks"), where("assignedTo", "==", uid))
    );
    const allTasks = snapList(tasksSnap);
    const tasks = allTasks.filter(t => t.status !== "completed").slice(0, 10);

    let pending_orders = [];
    let low_stock_alerts = [];
    let branch = null;

    if (bid) {
      // ✅ Get pending/processing orders for this branch
      const oSnap = await getDocs(
        query(
          collection(db, "orders"),
          where("branchId", "==", bid),
          where("status", "in", ["pending", "processing"])
        )
      );
      pending_orders = snapList(oSnap)
        .sort((a, b) => (a.createdAt?.seconds || 0) - (b.createdAt?.seconds || 0))
        .slice(0, 10)
        .map(o => ({
          ...o,
          order_number: o.orderNumber || o.order_number || '—',
          customer: `${o.shipFirstName || ''} ${o.shipLastName || ''}`.trim() || o.guestEmail || 'Guest',
          grand_total: o.grandTotal || o.total_amount || 0,
        }));

      // ✅ Get low stock alerts
      const invSnap = await getDocs(
        query(collection(db, "branch_inventory"), where("branchId", "==", bid))
      );
      low_stock_alerts = snapList(invSnap)
        .filter(i => (i.quantity || 0) <= (i.reorderLevel || 10))
        .sort((a, b) => (a.quantity || 0) - (b.quantity || 0))
        .slice(0, 5);

      // ✅ Get branch info
      const bSnap = await getDoc(doc(db, "branches", bid));
      if (bSnap.exists()) branch = { id: bSnap.id, ...bSnap.data() };
    }

    return ok({
      tasks,
      pending_orders,
      low_stock_alerts,
      branch,
      userRole: role(),
      taskCount: allTasks.length,
      orderCount: pending_orders.length,
      alertCount: low_stock_alerts.length,
    });
  } catch (e) {
    console.error("getEmployeeDashboard error:", e);
    return { success: false, message: e.message };
  }
}


// ══════════════════════════════════════════════════════════════
//  REAL-TIME LISTENERS
// ══════════════════════════════════════════════════════════════
export function watchBranchInventory(branchDocId, callback) {
  const bid = (isSubAdmin() || isStaff()) ? branchId() : branchDocId;
  if (!bid) return () => {};
  const q = query(collection(db, "branch_inventory"), where("branchId", "==", bid));
  return onSnapshot(q, snap => callback(snapList(snap)));
}

export function watchPendingOrders(callback) {
  const bid = branchId();
  const q   = bid
    ? query(collection(db, "orders"), where("branchId", "==", bid), where("status", "in", ["pending","processing"]))
    : query(collection(db, "orders"), where("status", "in", ["pending","processing"]));
  return onSnapshot(q, snap => callback(snapList(snap)));
}

export function watchAllOrders(callback) {
  const bid = (isSubAdmin() || isStaff()) ? branchId() : null;
  const q   = bid
    ? query(collection(db, "orders"), where("branchId", "==", bid), orderBy("createdAt", "desc"), limit(50))
    : query(collection(db, "orders"), orderBy("createdAt", "desc"), limit(50));
  return onSnapshot(q, snap => callback(snapList(snap)));
}

export function watchBranchInventoryAlerts(callback) {
  const bid = branchId();
  if (!bid) return () => {};
  const q = query(collection(db, "branch_inventory"), where("branchId", "==", bid));
  return onSnapshot(q, snap => {
    const alerts = snapList(snap).filter(i => (i.quantity || 0) <= (i.reorderLevel || 10));
    callback(alerts);
  });
}

export function watchTasks(callback) {
  const uid = currentUid();
  const bid = branchId();
  if (!uid) return () => {};
  const q = isStaff()
    ? query(collection(db, "tasks"), where("assignedTo", "==", uid))
    : bid
      ? query(collection(db, "tasks"), where("branchId", "==", bid))
      : query(collection(db, "tasks"), orderBy("createdAt", "desc"), limit(50));
  return onSnapshot(q, snap => callback(snapList(snap)));
}

export function watchOverviewStats(callback) {
  const bid = (isSubAdmin() || isStaff()) ? branchId() : null;
  const q   = bid
    ? query(collection(db, "orders"), where("branchId", "==", bid))
    : collection(db, "orders");
  return onSnapshot(q, async () => {
    const stats = await getOverview();
    callback(stats);
  });
}

// Live listener: all branches summary (main admin only)
export function watchBranchesSummary(callback) {
  if (!isMainAdmin()) return () => {};
  return onSnapshot(collection(db, "branches"), async snap => {
    const branches = snapList(snap);
    callback(branches);
  });
}


// ══════════════════════════════════════════════════════════════
//  ACTION MAP  —  window.api({ action: '...', ...fields })
// ══════════════════════════════════════════════════════════════
const ACTION_MAP = {
  // Overview
  get_overview:              ()  => getOverview(),

  // Orders — STAFF processes
  get_orders:                p  => getOrders({ search: p.search, status: p.status, branchFilter: p.branch_id }),
  get_transactions:          p  => getTransactions({ search: p.search, status: p.status, branchFilter: p.branch_id }),
  get_order_detail:          p  => getOrderDetail(p.id),
  get_pending_orders:        ()  => getPendingOrders(),
  accept_order:              p  => acceptOrder(p.id),
  process_order:             p  => processOrder(p.id, p.status),
  confirm_preparation:       p  => processOrder(p.id, "shipped"),
  update_order_status:       p  => processOrder(p.id, p.status),
  delete_order:              p  => deleteOrder(p.id),

  // Global product catalog — MAIN ADMIN only
  get_products:              p  => getProducts({ search: p.search, category: p.category, branchFilter: p.branch_id, invStatus: p.inv_status }),
  save_product:              p  => saveProduct(p),
  delete_product:            p  => deleteProduct(p.id),
  approve_product:           p  => approveProduct(p.id),
  reject_product:            p  => rejectProduct(p.id),
  get_pending_approvals:     ()  => getPendingApprovals(),
  restock:                   p  => restock(p.product_id, p.qty),

  // Branch inventory — SUB ADMIN (own branch) | MAIN ADMIN (all)
  get_inventory:             p  => getBranchInventory({ branchFilter: p.branch_id, search: p.search }),
  get_branch_inventory:      p  => getBranchInventory({ branchFilter: p.branch_id, search: p.search }),
  add_branch_inventory:      p  => addBranchInventory(p),
  update_inventory_stock:    p  => updateInventoryStock(p.id, p.quantity, p.reorder_level),
  delete_branch_inventory:   p  => deleteBranchInventory(p.id),
  transfer_inventory:        p  => transferInventory(p),
  get_transfer_logs:         p  => getTransferLogs(p.branch_id),

  // Branches — MAIN ADMIN only
  get_branches:              ()  => getBranches(),
  get_branches_simple:       ()  => getBranchesSimple(),
  get_branch_detail:         p  => getBranchDetail(p.id),
  add_branch:                p  => addBranch(p),
  update_branch:             p  => updateBranch(p.id, p),
  delete_branch:             p  => deleteBranch(p.id),
  set_main_branch:           p  => setMainBranch(p.id),

  // Users
  get_users:                 p  => getUsers({ search: p.search, status: p.status, filterRole: p.role, branchFilter: p.branch_id }),
  get_users_simple:          p  => getUsersSimple(p.branch_id),
  get_products_simple:       ()  => getProductsSimple(),
  inventory_lookup:          p  => inventoryLookup(p.q),
  add_user:                  p  => addUser(p),
  update_user:               p  => updateUser(p.id, p),
  delete_user:               p  => deleteUser(p.id),
  toggle_user_status:        p  => toggleUserStatus(p.id, p.status),
  assign_sub_admin:          p  => assignSubAdmin(p.user_id, p.branch_id),
  assign_employee_branch:    p  => assignEmployeeBranch(p.user_id, p.branch_id, p.position, p.department),
  get_sub_admins:            ()  => getSubAdmins(),
  get_employees:             p  => getEmployees({ branchFilter: p.branch_id, roleFilter: p.role }),

  // Branch staff
  get_branch_staff:          p  => getBranchStaff(p.branch_id),
  remove_branch_staff:       p  => removeBranchStaff(p.user_id, p.branch_id),

  // Tasks
  get_tasks:                 p  => getTasks({ branchFilter: p.branch_id }),
  add_task:                  p  => addTask(p),
  update_task_status:        p  => updateTaskStatus(p.id, p.status),
  delete_task:               p  => deleteTask(p.id),

  // Analytics
  get_analytics:             ()  => getAnalytics(),
  get_branch_analytics:      p  => getBranchAnalytics(p.branch_id),
  get_branch_sales:          ()  => getAnalytics(),
  get_employee_activity:     p  => getEmployees({ branchFilter: p.branch_id }),
  get_employee_dashboard:    ()  => getEmployeeDashboard(),
};

export async function firestoreApi(params = {}) {
  const action  = (params instanceof FormData ? params.get("action") : params.action) || "";
  const handler = ACTION_MAP[action];

  if (!handler) {
    console.warn(`admin_firestore.js: unknown action "${action}"`);
    return { success: false, message: `Unknown action: ${action}` };
  }

  let p = params;
  if (params instanceof FormData) {
    p = {};
    params.forEach((v, k) => { p[k] = v; });
  }

  return handler(p);
}
export async function getBranchAnalytics(branchFilter = "") {
  try {
    const ordersSnap = await getDocs(collection(db, "orders"));
    let orders = snapList(ordersSnap);
    
    const branchesSnap = await getDocs(collection(db, "branches"));
    const branches = snapList(branchesSnap);
    
    const branch_performance = branches.map(b => {
      const branchOrders = orders.filter(o => o.branchId === b.id && o.status !== "cancelled");
      const revenue = branchOrders.reduce((s, o) => s + (parseFloat(o.grandTotal) || 0), 0);
      return {
        id: b.id,
        name: b.name,
        city: b.city,
        is_main: b.isMain,
        revenue,
        orders: branchOrders.length,
        avg_order: branchOrders.length ? revenue / branchOrders.length : 0,
        staff_count: 0, // TODO: query branch_staff
        total_stock: 0, // TODO: query branch_inventory
        low_stock: 0,
        status: b.status,
      };
    });
    
    const total_revenue = orders
      .filter(o => o.status !== "cancelled")
      .reduce((s, o) => s + (parseFloat(o.grandTotal) || 0), 0);
    
    const revenue_pct = branch_performance.map(b => ({
      ...b,
      revenue_pct: total_revenue ? Math.round(b.revenue / total_revenue * 100) : 0,
    }));
    
    return ok({ branch_performance: revenue_pct });
  } catch (e) {
    return { success: false, message: e.message };
  }
}
if (!window._firestoreApi) {
  window._firestoreApi = firestoreApi;
}