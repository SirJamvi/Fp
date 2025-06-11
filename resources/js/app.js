// resources/js/app.js

// Import Bootstrap (assuming you are using it)
import 'bootstrap';

// Import your custom modules
import './pelayan_dashboard/main.js';
import './pelayan_dashboard/cart_manager.js';
import './pelayan_dashboard/menu_filter.js';
import './pelayan_dashboard/table_info.js';
import './pelayan_dashboard/form_submit.js';
import './pelayan_dashboard/payment_modal.js';


// midtrans_integration.js tidak perlu diimpor di sini
// karena fungsi triggerMidtransSnap diakses melalui import di form_submit.js
// dan library Snap itu sendiri dimuat secara global via tag <script> di Blade.

console.log('app.js loaded and custom modules imported.');
