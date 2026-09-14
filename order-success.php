<?php $pageTitle='Order Placed!'; ?>
<?php include 'includes/header.php'; ?><?php include 'includes/navbar.php'; ?>
<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;">
  <div style="text-align:center;max-width:500px;">
    <div style="font-size:80px;margin-bottom:16px;">🎉</div>
    <h1 style="font-size:32px;font-weight:900;color:#111827;">Order Placed Successfully!</h1>
    <p style="color:#64748B;font-size:16px;margin-top:12px;">Thank you for your order. We'll process it right away.</p>
    <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:14px;padding:20px;margin:24px 0;">
      <div style="font-size:14px;color:#64748B;">Order ID</div>
      <div id="orderIdDisplay" style="font-size:22px;font-weight:800;color:#16A34A;margin-top:4px;">—</div>
    </div>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a href="order-tracking.php" class="btn-primary">Track Order</a>
      <a href="products.php" class="btn-secondary">Continue Shopping</a>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
<script>document.addEventListener('DOMContentLoaded',function(){document.getElementById('orderIdDisplay').textContent=localStorage.getItem('detech_last_order')||'—';});</script>
