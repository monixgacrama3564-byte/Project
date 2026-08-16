// addresses.js
// Replaces: addresses.php
// Firestore path: userAddresses/{uid}/addresses/{addressId}

import { auth, db } from "./firebase-config.js";
import {
  collection,
  doc,
  addDoc,
  getDocs,
  deleteDoc,
  updateDoc,
  query,
  orderBy,
  serverTimestamp,
  writeBatch,
} from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

// ─────────────────────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────────────────────
function addrCol(uid) {
  // Path: userAddresses/{uid}/addresses
  return collection(db, "userAddresses", uid, "addresses");
}

function isValidName(n) {
  return /^[\p{L}\s'\-\.]+$/u.test(n);
}

function isValidPhone(p) {
  return !p || /^(\+63|0)[0-9\s\-]{9,14}$/.test(p);
}

function isValidPostal(p) {
  return !p || /^[0-9]{4,10}$/.test(p);
}

// ─────────────────────────────────────────────────────────────
// GET ALL ADDRESSES (sorted: default first, then by date)
// ─────────────────────────────────────────────────────────────
export async function getAddresses() {
  const user = auth.currentUser;
  if (!user) return [];

  try {
    const q = query(
      addrCol(user.uid),
      orderBy("isDefault", "desc"),
      orderBy("createdAt", "asc")
    );
    const snap = await getDocs(q);
    return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
  } catch (err) {
    console.error("getAddresses error:", err);
    return [];
  }
}

// ─────────────────────────────────────────────────────────────
// SAVE ADDRESS (new or update)
// ─────────────────────────────────────────────────────────────
export async function saveAddress(fields) {
  const user = auth.currentUser;
  if (!user) {
    return { success: false, message: "Please log in first." };
  }

  const {
    id = "",
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
    isDefault = false,
  } = fields;

  // Validation
  if (!firstName) return { success: false, message: "First name is required." };
  if (!isValidName(firstName)) return { success: false, message: "First name contains invalid characters." };
  if (!lastName) return { success: false, message: "Last name is required." };
  if (!isValidName(lastName)) return { success: false, message: "Last name contains invalid characters." };
  if (!address || address.length < 5) return { success: false, message: "Please enter a complete street address." };
  if (!city) return { success: false, message: "City is required." };
  if (!isValidPhone(phone)) return { success: false, message: "Enter a valid PH phone (e.g., 09XX XXX XXXX)." };
  if (!isValidPostal(postalCode)) return { success: false, message: "Postal code must be 4–10 digits." };

  try {
    const col = addrCol(user.uid);
    const snap = await getDocs(col);

    // Check for duplicate address (same address+city+province+postal)
    const isDuplicate = snap.docs.some((d) => {
      if (id && d.id === id) return false; // Skip self when updating
      const a = d.data();
      return (
        a.address.toLowerCase() === address.toLowerCase() &&
        a.apartment.toLowerCase() === apartment.toLowerCase() &&
        a.city.toLowerCase() === city.toLowerCase() &&
        a.province.toLowerCase() === province.toLowerCase() &&
        a.postalCode.toLowerCase() === postalCode.toLowerCase()
      );
    });

    if (isDuplicate) {
      return { success: false, message: "This address already exists." };
    }

    const addressData = {
      firstName,
      lastName,
      company,
      address,
      apartment,
      city,
      province,
      postalCode,
      phone,
      country,
      isDefault: !!isDefault,
      updatedAt: serverTimestamp(),
    };

    // If setting as default, clear existing defaults
    if (isDefault) {
      const batch = writeBatch(db);
      snap.docs.forEach((d) => {
        if (d.data().isDefault) {
          batch.update(d.ref, { isDefault: false });
        }
      });
      await batch.commit();
    }

    // Insert new address or update existing
    if (id) {
      // Update existing
      await updateDoc(doc(db, "userAddresses", user.uid, "addresses", id), addressData);
    } else {
      // Create new
      addressData.createdAt = serverTimestamp();
      await addDoc(col, addressData);
    }

    return { success: true, message: "Address saved successfully!" };

  } catch (err) {
    console.error("saveAddress error:", err);
    return { success: false, message: "Could not save address. Please try again." };
  }
}

// ─────────────────────────────────────────────────────────────
// DELETE ADDRESS
// ─────────────────────────────────────────────────────────────
export async function deleteAddress(addressId) {
  const user = auth.currentUser;
  if (!user) {
    return { success: false, message: "Please log in." };
  }

  try {
    await deleteDoc(doc(db, "userAddresses", user.uid, "addresses", addressId));
    return { success: true, message: "Address deleted." };
  } catch (err) {
    console.error("deleteAddress error:", err);
    return { success: false, message: "Could not delete address." };
  }
}

// ─────────────────────────────────────────────────────────────
// SET DEFAULT ADDRESS
// ─────────────────────────────────────────────────────────────
export async function setDefaultAddress(addressId) {
  const user = auth.currentUser;
  if (!user) {
    return { success: false, message: "Please log in." };
  }

  try {
    const snap = await getDocs(addrCol(user.uid));
    const batch = writeBatch(db);

    snap.docs.forEach((d) => {
      batch.update(d.ref, { isDefault: d.id === addressId });
    });

    await batch.commit();
    return { success: true };
  } catch (err) {
    console.error("setDefaultAddress error:", err);
    return { success: false, message: "Could not update default address." };
  }
}

// ─────────────────────────────────────────────────────────────
// RENDER ADDRESSES ON PAGE
// Call after addresses.php DOMContentLoaded
// ─────────────────────────────────────────────────────────────
export async function renderAddressesPage() {
  const container = document.getElementById("addressList");
  if (!container) return;

  container.innerHTML = '<p style="color:#888;text-align:center;padding:20px;">Loading addresses...</p>';

  const addresses = await getAddresses();

  if (addresses.length === 0) {
    container.innerHTML =
      '<p style="color:#888;text-align:center;padding:20px;"><i class="fas fa-inbox"></i> No saved addresses yet. <a href="addresses.php?action=add" style="color:#003da5;font-weight:600;">Add one now</a>.</p>';
    return;
  }

  container.innerHTML = addresses
    .map(
      (a) => `
    <div class="address-card" style="border:1px solid #e0e0e0;border-radius:6px;padding:18px 20px;margin-bottom:14px;background:#fff;position:relative;">
      ${a.isDefault ? '<span style="position:absolute;top:14px;right:16px;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:20px;padding:2px 12px;font-size:11px;font-weight:700;">✓ Default</span>' : ""}
      <p style="font-size:14px;font-weight:600;color:#222;margin-bottom:6px;margin-top:0;">${a.firstName} ${a.lastName}</p>
      <p style="font-size:13px;color:#555;line-height:1.7;margin:0;">
        ${a.address}${a.apartment ? ", " + a.apartment : ""}<br>
        ${a.city}${a.province ? ", " + a.province : ""}${a.postalCode ? " " + a.postalCode : ""}<br>
        ${a.country}<br>
        ${a.phone ? "📞 " + a.phone : ""}
      </p>
      <div style="margin-top:12px;display:flex;gap:12px;">
        ${!a.isDefault ? `<button onclick="window.handleSetDefault('${a.id}')" style="font-size:12px;color:#003da5;background:none;border:none;cursor:pointer;padding:0;text-decoration:underline;">Set as default</button>` : ""}
        <button onclick="window.handleDeleteAddress('${a.id}')" style="font-size:12px;color:#cc0000;background:none;border:none;cursor:pointer;padding:0;text-decoration:underline;">Delete</button>
      </div>
    </div>
  `
    )
    .join("");
}

// Global handlers for inline onclick buttons
window.handleDeleteAddress = async (id) => {
  if (!confirm("Delete this address?")) return;
  const result = await deleteAddress(id);
  if (result.success) {
    renderAddressesPage();
  } else {
    alert(result.message);
  }
};

window.handleSetDefault = async (id) => {
  const result = await setDefaultAddress(id);
  if (result.success) {
    renderAddressesPage();
  } else {
    alert(result.message);
  }
};

// ─────────────────────────────────────────────────────────────
// INITIALIZE ADDRESS FORM (wire to #addrForm submit)
// ─────────────────────────────────────────────────────────────
export function initAddressForm() {
  const form = document.querySelector("form#addrForm");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const get = (name) => form.querySelector(`[name="${name}"]`)?.value.trim() || "";
    const result = await saveAddress({
      id: get("address_id"),
      firstName: get("first_name"),
      lastName: get("last_name"),
      company: get("company"),
      address: get("address"),
      apartment: get("apartment"),
      city: get("city"),
      province: get("province"),
      postalCode: get("postal_code"),
      phone: get("phone"),
      country: get("country") || "Philippines",
      isDefault: !!form.querySelector("[name='is_default']")?.checked,
    });

    const msgEl = document.getElementById("addrFormMsg") || (() => {
      const el = document.createElement("div");
      el.id = "addrFormMsg";
      form.prepend(el);
      return el;
    })();

    if (result.success) {
      msgEl.style.cssText = "padding:10px 14px;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:6px;margin-bottom:14px;font-size:13px;font-weight:600;";
      msgEl.textContent = "✓ " + result.message;
      setTimeout(() => {
        window.location.href = "addresses.php";
      }, 1500);
    } else {
      msgEl.style.cssText = "padding:10px 14px;background:#fdecea;color:#c62828;border:1px solid #ef9a9a;border-radius:6px;margin-bottom:14px;font-size:13px;font-weight:600;";
      msgEl.textContent = result.message;
    }
  });
}

// ─────────────────────────────────────────────────────────────
// AUTO-INIT ON PAGE LOAD
// ─────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  renderAddressesPage();
  initAddressForm();
});