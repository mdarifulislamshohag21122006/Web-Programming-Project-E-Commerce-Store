<?php
require_once 'includes/db.php';
require_once 'includes/products_data.php';
$pageTitle = 'Checkout';

// Handle AJAX order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);

    $name    = trim($data['name']    ?? '');
    $phone   = trim($data['phone']   ?? '');
    $city    = trim($data['city']    ?? '');
    $address = trim($data['address'] ?? '');
    $payment = $data['payment'] ?? 'cod';
    $items   = $data['items']   ?? [];
    $total   = (int)($data['total']  ?? 0);
    $coupon  = trim($data['coupon']  ?? '');
    $couponDiscount = (int)($data['couponDiscount'] ?? 0);

    if (!$name || !$phone || !$city || !$address || empty($items) || $total <= 0) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += (int)($item['price'] ?? 0) * (int)($item['qty'] ?? 0);
    }

    // Validate coupon if provided
    if ($coupon) {
        $couponRow = db_row('SELECT * FROM coupons WHERE code = ? AND is_active = 1', [strtoupper($coupon)]);
        if (
            !$couponRow ||
            (!empty($couponRow['expires_at']) && $couponRow['expires_at'] < date('Y-m-d')) ||
            ($couponRow['max_uses'] !== null && (int)$couponRow['used_count'] >= (int)$couponRow['max_uses']) ||
            ((int)$couponRow['min_order'] > 0 && $subtotal < (int)$couponRow['min_order'])
        ) { $coupon = ''; $couponDiscount = 0; }
        else {
            $couponDiscount = (int)$couponRow['discount'];
            db_run('UPDATE coupons SET used_count = used_count + 1 WHERE code = ?', [strtoupper($coupon)]);
        }
    } else {
        $couponDiscount = 0;
    }

    $total = $subtotal - (int)round($subtotal * $couponDiscount / 100);

    $orderId   = 'ORD-' . date('Y') . '-' . rand(100000, 999999);
    $trackingId = 'TRK' . strtoupper(substr(md5(uniqid()), 0, 9));
    $fullAddress = "$name, $phone, $address, $city";

    $id = db_run(
        'INSERT INTO orders (order_key, user_id, total, coupon_code, coupon_discount, address, payment_method, tracking_id)
         VALUES (?,?,?,?,?,?,?,?)',
        [$orderId, current_user_id(), $total, $coupon ?: null, $couponDiscount, $fullAddress, $payment, $trackingId]
    );

    foreach ($items as $item) {
        db_run(
            'INSERT INTO order_items (order_id, product_id, product_name, product_image, product_brand, price, quantity)
             VALUES (?,?,?,?,?,?,?)',
            [$id, $item['id'], $item['name'], $item['image'], $item['brand'] ?? '', $item['price'], $item['qty']]
        );
    }

    echo json_encode(['success' => true, 'orderId' => $orderId, 'trackingId' => $trackingId]);
    exit;
}
?>
<?php include 'includes/header.php'; ?><?php include 'includes/navbar.php'; ?>
<div class="page-hero"><div class="container"><h1>✅ Checkout</h1><p>Complete your order</p></div></div>
<div class="container">
  <div id="emptyCheck" style="display:none;padding:80px 0;text-align:center;"><div style="font-size:80px;">🛒</div><div style="font-size:24px;font-weight:800;margin-top:16px;">Your cart is empty</div><a href="products.php" class="btn-primary" style="margin-top:24px;display:inline-flex;">Browse Products</a></div>
  <div class="checkout-layout" id="checkoutLayout">
    <div>
      <div class="checkout-section">
        <div class="checkout-section-title">📍 Delivery Address</div>
        <div class="checkout-form-grid">
          <div class="form-group"><label class="form-label">Full Name *</label><input type="text" id="fname" class="form-input" placeholder="Your full name" value="<?php echo htmlspecialchars(session_user()['name'] ?? ''); ?>"></div>
          <div class="form-group"><label class="form-label">Phone *</label><input type="tel" id="fphone" class="form-input" placeholder="+880 " value="<?php echo htmlspecialchars(session_user()['phone'] ?? ''); ?>"></div>
          <div class="form-group"><label class="form-label">Email</label><input type="email" id="femail" class="form-input" placeholder="you@example.com" value="<?php echo htmlspecialchars(session_user()['email'] ?? ''); ?>"></div>
          <div class="form-group"><label class="form-label">City *</label>
            <select id="fcity" class="form-input"><option value="">Select City</option><?php foreach([
'Bagerhat','Bandarban','Barguna','Barisal','Bhola','Bogura','Brahmanbaria',
'Chandpur','Chattogram','Chuadanga','Cox\'s Bazar','Cumilla','Dhaka',
'Dinajpur','Faridpur','Feni','Gaibandha','Gazipur','Gopalganj','Habiganj',
'Jamalpur','Jashore','Jhalokathi','Jhenaidah','Joypurhat','Khagrachari',
'Khulna','Kishoreganj','Kurigram','Kushtia','Lakshmipur','Lalmonirhat',
'Madaripur','Magura','Manikganj','Meherpur','Moulvibazar','Munshiganj',
'Mymensingh','Naogaon','Narail','Narayanganj','Narsingdi','Natore',
'Netrokona','Nilphamari','Noakhali','Pabna','Panchagarh','Patuakhali',
'Pirojpur','Rajbari','Rajshahi','Rangamati','Rangpur','Satkhira',
'Shariatpur','Sherpur','Sirajganj','Sunamganj','Sylhet','Tangail',
'Thakurgaon'
] as $c): ?><option><?php echo $c;?></option><?php endforeach; ?></select>
          </div>
          <div class="form-group" style="grid-column:span 2;"><label class="form-label">Street Address *</label><input type="text" id="faddress" class="form-input" placeholder="House/Flat no., Street, Area"></div>
        </div>
      </div>
      <div class="checkout-section">
        <div class="checkout-section-title">💳 Payment Method</div>
        <div class="payment-option active" id="pay-cod" onclick="selectPayment('cod')"><input type="radio" name="payment" checked><label style="cursor:pointer;"><div style="font-weight:700;">💵 Cash on Delivery (COD)</div><div style="font-size:13px;color:#64748B;">Pay when your order arrives</div></label></div>
        <div class="payment-option" id="pay-card" onclick="selectPayment('card')"><input type="radio" name="payment"><label style="cursor:pointer;"><div style="font-weight:700;">💳 Debit / Credit Card</div><div style="font-size:13px;color:#64748B;">Visa, Mastercard, Nexuspay</div></label></div>
        <div id="cardFields" style="display:none;margin-top:16px;">
          <div class="checkout-form-grid">
            <div class="form-group" style="grid-column:span 2;"><label class="form-label">Card Number</label><input type="text" class="form-input" placeholder="1234 5678 9012 3456" maxlength="19" oninput="let v=this.value.replace(/\D/g,'').substring(0,16);this.value=v.replace(/(.{4})/g,'$1 ').trim();"></div>
            <div class="form-group"><label class="form-label">Expiry</label><input type="text" class="form-input" placeholder="MM / YY" maxlength="7"></div>
            <div class="form-group"><label class="form-label">CVV</label><input type="text" class="form-input" placeholder="123" maxlength="4"></div>
          </div>
        </div>
        <div class="payment-option" id="pay-easypaisa" onclick="selectPayment('easypaisa')"><input type="radio" name="payment"><label style="cursor:pointer;"><div style="font-weight:700;">📱 Mobile Banking (Bkash / Roket / Nagad)</div><div style="font-size:13px;color:#64748B;">Mobile wallet payment</div></label></div>
      </div>
    </div>
    <div>
      <div class="summary-box">
        <div class="summary-title">Order Summary</div>
        <div id="checkoutItems"></div>
        <hr style="margin:16px 0;border:none;border-top:1.5px solid #E2E8F0;">
        <div class="summary-row"><span>Subtotal</span><span id="coSubtotal">BDT 0</span></div>
        <div class="summary-row"><span>Delivery</span><span style="color:#10B981;">FREE</span></div>
        <div class="summary-row" id="coCouponRow" style="display:none;color:#10B981;"><span>Coupon</span><span id="coCouponAmt"></span></div>
        <div class="summary-row total"><span>Total</span><span id="coTotal" style="color:#F97316;">BDT 0</span></div>
        <button onclick="placeOrderNow()" class="btn-primary" style="width:100%;justify-content:center;margin-top:20px;display:flex;padding:16px;" id="placeBtn">Place Order 🎉</button>
        <p style="color:#94A3B8;font-size:12px;text-align:center;margin-top:12px;">🛡 Secure checkout. 7-day easy returns.</p>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
<script>
let selectedPayment = 'cod';
function selectPayment(type) {
  selectedPayment = type;
  document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('active'));
  document.getElementById('pay-' + type).classList.add('active');
  document.getElementById('cardFields').style.display = type === 'card' ? 'block' : 'none';
}
function renderCheckout() {
  const cart = getCart();
  if (!cart.length) { document.getElementById('emptyCheck').style.display='block'; document.getElementById('checkoutLayout').style.display='none'; return; }
  const container = document.getElementById('checkoutItems'); container.innerHTML = '';
  cart.forEach(item => { const d=document.createElement('div');d.className='checkout-item';d.innerHTML=`<img src="${item.image}" alt="${item.name}"><div style="flex:1;"><div style="font-weight:600;font-size:14px;">${item.name}</div><div style="color:#64748B;font-size:13px;">Qty: ${item.qty}</div></div><div style="font-weight:700;">${formatPrice(item.price*item.qty)}</div>`;container.appendChild(d); });
  const sub=getCartTotal(),disc=getCouponDiscount(),save=Math.round(sub*disc/100),total=sub-save;
  document.getElementById('coSubtotal').textContent=formatPrice(sub);
  document.getElementById('coTotal').textContent=formatPrice(total);
  if(getCoupon()&&disc>0){document.getElementById('coCouponRow').style.display='flex';document.getElementById('coCouponAmt').textContent='−'+formatPrice(save);}
}
async function placeOrderNow() {
  const name=document.getElementById('fname').value.trim(),phone=document.getElementById('fphone').value.trim(),city=document.getElementById('fcity').value,addr=document.getElementById('faddress').value.trim();
  if(!name||!phone||!city||!addr){showToast('Please fill all required fields','error');return;}
  const btn=document.getElementById('placeBtn');btn.disabled=true;btn.textContent='Placing Order...';
  const sub=getCartTotal(),disc=getCouponDiscount(),save=Math.round(sub*disc/100);
  const payload={name,phone,city,address:addr,payment:selectedPayment,items:getCart(),total:sub-save,coupon:getCoupon()||'',couponDiscount:disc};
  try {
    const res = await fetch('checkout.php', {method:'POST',headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(payload)});
    const data = await res.json();
    if(data.success){
      clearCart(); removeCoupon();
      localStorage.setItem('detech_last_order', data.orderId);
      window.location.href='order-success.php';
    } else { showToast(data.error||'Order failed. Try again.','error'); btn.disabled=false;btn.textContent='Place Order 🎉'; }
  } catch(e){ showToast('Network error. Please try again.','error');btn.disabled=false;btn.textContent='Place Order 🎉'; }
}
document.addEventListener('DOMContentLoaded', renderCheckout);
</script>
