// resources/js/pelayan_dashboard/main.js

// Import initialization functions from other modules
import { initCart, addItemToCart } from './cart_manager';
import { initMenuFilter, attachAddToCartListeners } from './menu_filter';
import { initTableInfo } from './table_info';
import { initFormSubmit, checkSubmitButtonStatus } from './form_submit';
import { initPaymentModal } from './payment_modal';
import { showCustomAlert } from '../utils';

// Tunggu hingga DOM sepenuhnya dimuat
document.addEventListener('DOMContentLoaded', () => {
  console.log('Pelayan Dashboard main.js: DOMContentLoaded');

  // Elemen untuk Order Dashboard
  const orderEls = {
    cartItemsContainer:       document.getElementById('cartItems'),
    emptyCartMessage:         document.getElementById('emptyCartMessage'),
    totalItemsSpan:           document.getElementById('totalItems'),
    grandTotalSpan:           document.getElementById('grandTotal'),
    hiddenInputsContainer:    document.getElementById('hiddenInputs'),
    submitOrderBtn:           document.getElementById('submitOrderBtn'),
    menuSearchInput:          document.getElementById('menuSearch'),
    categoryTabs:             document.getElementById('categoryTabs'),
    areaSelect:               document.getElementById('area'),
    mejaSelect:               document.getElementById('meja'),
    selectedTableInfoDiv:     document.getElementById('selectedTableInfo'),
    jumlahTamuInput:          document.getElementById('jumlah_tamu'),
    orderForm:                document.getElementById('orderForm'),
    processPaymentRouteInput: document.getElementById('processPaymentRoute'),
    orderSummaryRouteInput:   document.getElementById('orderSummaryRoute'),
    paymentModalEl:           document.getElementById('paymentModal'),
    paymentOptions:           document.getElementById('paymentOptions'),
    cashPaymentForm:          document.getElementById('cashPaymentForm'),
    qrisPaymentInfo:          document.getElementById('qrisPaymentInfo'),
    uangDiterimaInput:        document.getElementById('uangDiterima'),
    kembalianDisplay:         document.getElementById('kembalianDisplay'),
    btnBayarCash:             document.getElementById('btnBayarCash'),
    btnConfirmQris:           document.getElementById('btnConfirmQris'),
    btnBackToOptions:         document.getElementById('btnBackToOptions'),
    btnBackToOptionsQris:     document.getElementById('btnBackToOptionsQris'),
    loadingIndicator:         document.getElementById('loadingIndicator'),
    modalTotalBillSpan:       document.getElementById('modalTotalBill'),
    modalKodeOrderStrong:     document.getElementById('modalKodeOrder'),
    paymentSuccessMessage:    document.getElementById('paymentSuccessMessage'),
    paymentErrorMessage:      document.getElementById('paymentErrorMessage'),
    btnCash:                  document.getElementById('btnCash'),
    btnQris:                  document.getElementById('btnQris'),
    paymentSuccessActions:    document.getElementById('paymentSuccessActions'),
    btnBackToDashboard:       document.getElementById('btnBackToDashboard'),
    btnViewSummary:           document.getElementById('btnViewSummary'),
  };

  // Elemen untuk Table Management
  const tableWrapper = document.getElementById('tableManagementWrapper');

  // DETEKSI HALAMAN ORDER
  const isOrderPage = orderEls.orderForm && orderEls.cartItemsContainer;
  if (isOrderPage) {
    console.log('Initializing Order Dashboard modules...');
    initCart(orderEls);
    initMenuFilter(orderEls);
    attachAddToCartListeners(addItemToCart);
    initTableInfo(orderEls);
    initFormSubmit(orderEls);
    checkSubmitButtonStatus();
    initPaymentModal(orderEls);
    console.log('Order Dashboard initialized.');
  }

  // DETEKSI HALAMAN TABLE MANAGEMENT
  if (tableWrapper) {
    console.log('Initializing Table Management module...');
    initTableInfo({ tableWrapper });
    console.log('Table Management initialized.');
  }

  if (!isOrderPage && !tableWrapper) {
    console.warn('No matching page context found.');
  }
});
