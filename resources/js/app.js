import './bootstrap';
import './components/darkModeSwitcher'
import './encounters/datepicker.js'; // Or the correct path to your file
import './encounters/mic-test';

import 'flowbite';

import {
    // Datepicker,
    // Select,
    Input,
    Ripple,
    initTE
} from "tw-elements";
initTE({ Ripple });
// initTE({ Datepicker, Select, Input, Ripple });

/* import bootstrap */
// import * as bootstrap from 'bootstrap';

import Alpine from 'alpinejs';


window.Alpine = Alpine;

// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
    apiKey: "AIzaSyBdm_89XhsctbMtMJBgDf7D1TtvfZ38XH8",
    authDomain: "talkingvet-dictation-portal.firebaseapp.com",
    projectId: "talkingvet-dictation-portal",
    storageBucket: "talkingvet-dictation-portal.appspot.com",
    messagingSenderId: "624239984865",
    appId: "1:624239984865:web:054cffde59da585796f7a0",
    measurementId: "G-8J4LP9F17M"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);

Alpine.start();
