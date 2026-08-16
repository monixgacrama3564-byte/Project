// firebase-config.js — Modular Firebase SDK v10.12.0
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
import { getFirestore } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";

const firebaseConfig = {
  apiKey:            "AIzaSyC-X5U_Vlgn6vcG__xKvW49-hA7luhWLDI",
  authDomain:        "pcexpress-6ea42.firebaseapp.com",
  projectId:         "pcexpress-6ea42",
  storageBucket:     "pcexpress-6ea42.firebasestorage.app",
  messagingSenderId: "908206035607",
  appId:             "1:908206035607:web:ff8bf2f9ba8a8322d48fd2",
  measurementId:     "G-EHNKLL6911"
};

const app  = initializeApp(firebaseConfig);
const db   = getFirestore(app);
const auth = getAuth(app);

export const firestoreReady = Promise.resolve(db);
export { app, db, auth };
