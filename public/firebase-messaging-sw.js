// firebase-messaging-sw.js

// importScripts('https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js');
// importScripts('https://www.gstatic.com/firebasejs/9.22.2/firebase-messaging-compat.js');

// const firebaseConfig = {
//     apiKey: "AIzaSyAo8Ozpy2ZSKTnR1K2C1rs4iq-bxj4X2oc",
//     projectId: "fill-in-test",
//     messagingSenderId: "1026549752959",
//     appId: "1:1026549752959:web:1067aec26f25178eb00b4a",
// };

// // Initialize Firebase
// const app = firebase.initializeApp(firebaseConfig);
// const messaging = firebase.messaging();

// // Background notification को handle करें
// messaging.onBackgroundMessage(function(payload) {
//   console.log('[firebase-messaging-sw.js] Received background message ', payload);

//   const notificationTitle = payload.notification.title;
//   const notificationOptions = {
//     body: payload.notification.body,
//     icon: '/icon.png' // अगर कोई icon है
//   };

//   self.registration.showNotification(notificationTitle, notificationOptions);
// });


importScripts('https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.22.2/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "AIzaSyAo8Ozpy2ZSKTnR1K2C1rs4iq-bxj4X2oc",
    projectId: "fill-in-test",
    messagingSenderId: "1026549752959",
    appId: "1:1026549752959:web:1067aec26f25178eb00b4a",
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);

  const notificationTitle = payload.data.title;
  const notificationOptions = {
    body: payload.data.body,
    icon: '/icon.png',
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});

