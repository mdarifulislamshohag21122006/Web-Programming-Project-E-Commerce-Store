<?php
require_once 'includes/products_data.php';
$pageTitle = 'Home';
$flashSaleProducts = array_values(array_filter($products, fn($p) => $p['isFlashSale']));
$trendingProducts = array_values(array_filter($products, fn($p) => $p['isTrending']));
$bestSellers = array_values(array_filter($products, fn($p) => $p['isBestSeller']));
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<section class="hero" id="heroSection">
  <div class="hero-deco1"></div><div class="hero-deco2"></div>
  <div class="container hero-content">
    <div class="hero-grid">
      <div>
        <span class="hero-badge" id="heroBadge">⚡ Flash Sale</span>
        <h1 class="hero-title" id="heroTitle">iPhone 15 Pro Max</h1>
        <p class="hero-subtitle" id="heroSubtitle">The most powerful iPhone ever</p>
        <p class="hero-desc" id="heroDesc">A17 Pro chip. Titanium design. Pro camera system.</p>
        <div class="hero-prices">
          <div><div class="hero-price-old" id="heroPriceOld">BDT 189,999</div><div class="hero-price-new" id="heroPriceNew">BDT 169,999</div></div>
          <span class="hero-price-badge" id="heroDiscount">23% OFF</span>
        </div>
        <div class="hero-btns">
          <a href="product.php?id=p1" id="heroCta" class="btn-primary">Shop Now →</a>
          <a href="products.php" class="btn-outline-white">View All Deals</a>
        </div>
        <div class="hero-trust">
          <div class="hero-trust-item">🛡 Genuine Product</div>
          <div class="hero-trust-item">🚚 Free Delivery</div>
          <div class="hero-trust-item">↺ 7-Day Return</div>
        </div>
      </div>
      <div class="hero-img-wrap">
        <div style="position:relative;">
          <div class="hero-img-box"><img id="heroImg" src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500&auto=format" alt="Product"></div>
          <div class="hero-float-card"><div style="color:#F59E0B;">★★★★★</div><div style="font-weight:700;font-size:13px;">4.9/5 · 2,847 reviews</div></div>
          <div class="hero-float-badge">✓ In Stock</div>
        </div>
      </div>
    </div>
    <div class="hero-dots">
      <button class="hero-dot active"></button>
      <button class="hero-dot"></button>
      <button class="hero-dot"></button>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="flash-sale-box">
      <div class="flash-sale-header">
        <div class="flash-sale-title">
          <div class="flash-sale-icon">⚡</div>
          <div><h2>⚡ Flash Sale</h2><p>Limited time deals — Don't miss out!</p></div>
        </div>
        <div class="flash-timer">
          <div class="timer-display">
            <div class="timer-block" id="timerH">06</div><span class="timer-sep">:</span>
            <div class="timer-block" id="timerM">00</div><span class="timer-sep">:</span>
            <div class="timer-block" id="timerS">00</div>
          </div>
          <a href="products.php?flashsale=1" class="btn-primary" style="padding:10px 18px;font-size:13px;">View All →</a>
        </div>
      </div>
      <div class="flash-grid">
        <?php foreach(array_slice($flashSaleProducts,0,4) as $p): ?>
        <a href="product.php?id=<?php echo $p['id']; ?>" class="flash-card">
          <img src="<?php echo $p['images'][0]; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" loading="lazy">
          <div class="flash-card-body">
            <div class="flash-card-name"><?php echo htmlspecialchars($p['name']); ?></div>
            <div class="flash-card-prices">
              <span class="flash-price">BDT <?php echo number_format($p['flashSalePrice']??$p['price']); ?></span>
              <span class="badge-sale">-<?php echo $p['discount']; ?>%</span>
            </div>
            <?php if(!empty($p['flashSaleStock'])): ?>
            <div class="flash-progress"><div class="flash-progress-bar" style="width:<?php echo round(($p['flashSaleSold']/$p['flashSaleStock'])*100); ?>%"></div></div>
            <?php endif; ?>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="section-title"><h2>Shop by Category</h2><a href="products.php">View All →</a></div>
    <div class="category-grid">
      <?php foreach($CATEGORIES as $cat): ?>
      <a href="products.php?category=<?php echo $cat['id']; ?>" class="category-card">
        <span class="cat-icon"><?php echo $cat['icon']; ?></span>
        <span class="cat-name"><?php echo $cat['name']; ?></span>
        <span class="cat-count"><?php echo $cat['count']; ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="section-title"><h2>📈 Trending Now</h2><a href="products.php">View All →</a></div>
    <div class="products-grid">
      <?php foreach(array_slice($trendingProducts,0,8) as $p): ?>
      <?php include 'includes/product_card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div style="background:#fff;padding:48px 0;">
  <div class="container">
    <div class="section-title"><h2>🏆 Best Sellers</h2><a href="products.php">View All →</a></div>
    <div class="products-grid-5">
      <?php foreach(array_slice($bestSellers,0,5) as $p): ?>
      <?php include 'includes/product_card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="promo-grid">
      <div class="promo-card" style="background:linear-gradient(135deg,#0F172A 0%,#1e3a5f 100%);">
        <span style="background:#F97316;color:#fff;border-radius:6px;padding:4px 12px;font-size:12px;font-weight:700;">NEW LAUNCH</span>
        <div style="color:#fff;font-weight:800;font-size:22px;margin-top:12px;">Gaming Setup<br>Essentials</div>
        <div style="color:#94A3B8;font-size:13px;margin-top:8px;">Complete your battlestation</div>
        <a href="products.php?category=gaming" class="btn-primary" style="margin-top:16px;display:inline-flex;">Explore Gaming →</a>
      </div>
      <div class="promo-card" style="background:linear-gradient(135deg,#F97316 0%,#ea580c 100%);">
        <span style="background:rgba(255,255,255,0.25);color:#fff;border-radius:6px;padding:4px 12px;font-size:12px;font-weight:700;">COD AVAILABLE</span>
        <div style="color:#fff;font-weight:800;font-size:22px;margin-top:12px;">Pay When<br>You Receive</div>
        <div style="color:rgba(255,255,255,0.8);font-size:13px;margin-top:8px;">Cash on Delivery on thousands of products</div>
        <a href="products.php?cod=1" style="background:#fff;color:#F97316;border-radius:8px;padding:10px 20px;font-size:14px;font-weight:600;display:inline-flex;margin-top:16px;">Shop with COD →</a>
      </div>
    </div>
  </div>
</section>

<div class="testimonials-bg">
  <div class="container">
    <div style="text-align:center;margin-bottom:28px;">
      <h2 style="font-size:28px;font-weight:800;">What Our Customers Say</h2>
      <p style="color:#64748B;margin-top:8px;">Over 50,000 happy customers across Bangladesh</p>
    </div>
    <div class="testimonials-grid">
      <?php foreach([
        ['name'=>'Rezaul Karim','city'=>'Rangamati','rating'=>5,'text'=>'Best online electronics store! Got my iPhone in 2 days. Genuine product, great service.','avatar'=>'https://i.pravatar.cc/48?img=11'],
        ['name'=>'Sana Haque','city'=>'Lalmanirhat','rating'=>5,'text'=>'Amazing experience! The Sony headphones are fantastic. COD option is very convenient.','avatar'=>'https://i.pravatar.cc/48?img=25'],
        ['name'=>'Tarikul Islam','city'=>'Ishwardi','rating'=>5,'text'=>'Flash sale prices are unbeatable. Got MacBook Pro at 20% off. Will shop again!','avatar'=>'https://i.pravatar.cc/48?img=33'],
        ['name'=>'Ayesha Khan','city'=>'Rajshahi','rating'=>4,'text'=>'Very professional. Easy checkout with COD. Packaging was excellent. Highly recommend!','avatar'=>'https://i.pravatar.cc/48?img=47'],
      ] as $t): ?>
      <div class="testimonial-card">
        <div class="stars"><?php echo str_repeat('★',$t['rating']); ?></div>
        <p class="testimonial-text">"<?php echo $t['text']; ?>"</p>
        <div class="testimonial-author">
          <img src="<?php echo $t['avatar']; ?>" alt="<?php echo $t['name']; ?>">
          <div><div class="testimonial-name"><?php echo $t['name']; ?></div><div class="testimonial-city"><?php echo $t['city']; ?></div></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="newsletter">
  <div class="container">
    <h2>Get Exclusive Deals</h2>
    <p>Subscribe and get 10% off your first order!</p>
    <form class="newsletter-form" onsubmit="event.preventDefault();showToast('Subscribed! Use DETECH10 for 10% off.','success');">
      <input type="email" class="newsletter-input" placeholder="Enter your email address" required>
      <button type="submit" class="btn-primary" style="padding:12px 22px;">Subscribe</button>
    </form>
    <p style="color:#64748B;font-size:12px;margin-top:12px;">No spam. Unsubscribe anytime.</p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
const heroSlides=[
  {title:'iPhone 15 Pro Max',subtitle:'The most powerful iPhone ever',desc:'A17 Pro chip. Titanium design. Pro camera system.',price:'BDT 189,999',salePrice:'BDT 169,999',discount:'23% OFF',image:'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500&auto=format',bg:'linear-gradient(135deg,#0F172A 0%,#1e3a5f 60%,#0F172A 100%)',cta:'product.php?id=p1',badge:'⚡ Flash Sale'},
  {title:'Samsung Galaxy S24 Ultra',subtitle:'Galaxy AI is here',desc:'Transform your world with Galaxy AI. 200MP camera.',price:'BDT 209,999',salePrice:'BDT 159,999',discount:'24% OFF',image:'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=500&auto=format',bg:'linear-gradient(135deg,#0F172A 0%,#1a1a2e 60%,#16213e 100%)',cta:'product.php?id=p2',badge:'🏆 Best Seller'},
  {title:'MacBook Pro M3 Pro',subtitle:'Built for Apple Intelligence',desc:'Supercharged by M3 Pro. Up to 18-hour battery.',price:'BDT 279,999',salePrice:'BDT 229,999',discount:'18% OFF',image:'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500&auto=format',bg:'linear-gradient(135deg,#0F172A 0%,#1e2d4f 60%,#0F172A 100%)',cta:'product.php?id=p5',badge:'🔥 Hot Deal'},
];
initHeroSlider(heroSlides);
initFlashTimer(<?php echo $FLASH_SALE_END; ?>);
</script>
