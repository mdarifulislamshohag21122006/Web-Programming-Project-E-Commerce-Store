<?php $pageTitle='My Cart'; ?>
<?php include 'includes/header.php'; ?><?php include 'includes/navbar.php'; ?>
<div class="page-hero"><div class="container"><h1>🛒 My Cart</h1><p>Review your items before checkout</p></div></div>
<div class="container">
  <div class="cart-layout" id="cartLayout">
    <div id="cartItems"><div class="empty-cart" id="emptyCart" style="display:none;"><div class="empty-icon">🛒</div><div class="empty-title">Your cart is empty</div><p class="empty-desc">Add some products to get started!</p><a href="products.php" class="btn-primary">Browse Products</a></div></div>
    <div class="summary-box" id="cartSummary" style="display:none;">
      <div class="summary-title">Order Summary</div>
      <div class="summary-row"><span>Subtotal</span><span id="summarySubtotal">BDT 0</span></div>
      <div class="summary-row"><span>Delivery</span><span style="color:#10B981;">FREE</span></div>
      <div class="summary-row" id="couponRow" style="display:none;color:#10B981;"><span>Coupon (<span id="couponCode"></span>)</span><span id="couponAmount"></span></div>
      <div class="summary-row total"><span>Total</span><span id="summaryTotal" style="color:#F97316;">BDT 0</span></div>
      <div class="coupon-form"><input type="text" id="couponInput" placeholder="e.g. DETECH10"><button onclick="tryCoupon()" class="btn-primary" style="padding:10px 14px;font-size:13px;">Apply</button></div>
      <div style="font-size:12px;color:#94A3B8;margin-bottom:16px;">Try: DETECH10, SAVE20, FLASH15</div>
      <a href="checkout.php" class="btn-primary" style="width:100%;justify-content:center;margin-bottom:10px;display:flex;">Proceed to Checkout →</a>
      <a href="products.php" class="btn-secondary" style="width:100%;justify-content:center;display:flex;">Continue Shopping</a>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
<script>
function renderCart(){
  const cart=getCart(),container=document.getElementById('cartItems'),summary=document.getElementById('cartSummary'),empty=document.getElementById('emptyCart');
  container.innerHTML='';
  if(!cart.length){empty.style.display='block';summary.style.display='none';container.appendChild(empty);return;}
  empty.style.display='none';summary.style.display='block';container.appendChild(empty);
  cart.forEach(item=>{
    const div=document.createElement('div');div.className='cart-item';
    div.innerHTML=`<div class="cart-item-img"><img src="${item.image}" alt="${item.name}"></div>
      <div class="cart-item-info"><div class="cart-item-name">${item.name}</div><div class="cart-item-brand">${item.brand}</div><div class="cart-item-price">${formatPrice(item.price)}</div>
        <div class="cart-item-actions"><button class="cart-qty-btn" onclick="updateCartQty('${item.id}',${item.qty-1})">−</button><span class="cart-qty-num">${item.qty}</span><button class="cart-qty-btn" onclick="updateCartQty('${item.id}',${item.qty+1})">+</button><button onclick="removeFromCart('${item.id}')" class="btn-danger">Remove</button></div>
      </div><div style="text-align:right;min-width:120px;"><div style="font-weight:800;font-size:17px;">${formatPrice(item.price*item.qty)}</div><div style="color:#94A3B8;font-size:13px;">${item.qty} × ${formatPrice(item.price)}</div></div>`;
    container.appendChild(div);
  });
  updateSummary();
}
function updateSummary(){
  const sub=getCartTotal(),disc=getCouponDiscount(),save=Math.round(sub*disc/100),total=sub-save;
  document.getElementById('summarySubtotal').textContent=formatPrice(sub);
  document.getElementById('summaryTotal').textContent=formatPrice(total);
  const coupon=getCoupon(),row=document.getElementById('couponRow');
  if(coupon&&disc>0){row.style.display='flex';document.getElementById('couponCode').textContent=coupon;document.getElementById('couponAmount').textContent='−'+formatPrice(save);}
  else row.style.display='none';
}
async function tryCoupon(){const code=document.getElementById('couponInput').value.trim();if(!code){showToast('Enter a coupon code','error');return;}const d=await applyCoupon(code);if(d)updateSummary();}
document.addEventListener('DOMContentLoaded',renderCart);
</script>
