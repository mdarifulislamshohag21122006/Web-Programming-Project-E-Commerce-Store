<?php
require_once 'includes/products_data.php';
$id=$_GET['id']??''; $p=getProductById($id);
if(!$p){header('Location: products.php');exit;}
$pageTitle=$p['name'];
$ep=$p['isFlashSale']&&isset($p['flashSalePrice'])?$p['flashSalePrice']:$p['price'];
$related=array_slice(array_values(array_filter($products,fn($x)=>$x['category']===$p['category']&&$x['id']!==$p['id'])),0,4);
?>
<?php include 'includes/header.php'; ?><?php include 'includes/navbar.php'; ?>
<div class="container">
  <div class="breadcrumb"><a href="index.php">Home</a><span class="sep">›</span><a href="products.php">Products</a><span class="sep">›</span><a href="products.php?category=<?php echo $p['category'];?>"><?php echo ucfirst($p['category']);?></a><span class="sep">›</span><span class="current"><?php echo htmlspecialchars($p['name']);?></span></div>
  <div class="product-detail-grid">
    <div class="product-images">
      <div class="main-img"><img src="<?php echo $p['images'][0];?>" alt="<?php echo htmlspecialchars($p['name']);?>" id="mainImg"></div>
      <div class="thumb-list">
        <?php foreach($p['images'] as $i=>$img): ?><div class="thumb <?php echo $i===0?'active':'';?>" onclick="switchImg('<?php echo $img;?>',this)"><img src="<?php echo $img;?>" alt=""></div><?php endforeach; ?>
      </div>
    </div>
    <div class="product-info">
      <div class="product-detail-brand"><?php echo htmlspecialchars($p['brand']);?></div>
      <h1 class="product-detail-name"><?php echo htmlspecialchars($p['name']);?></h1>
      <div class="product-detail-rating">
        <span class="stars" style="font-size:18px;"><?php echo str_repeat('★',floor($p['rating']));?></span>
        <span style="font-weight:700;"><?php echo $p['rating'];?></span>
        <span style="color:#64748B;font-size:14px;">(<?php echo number_format($p['reviewCount']);?> reviews)</span>
        <?php if(!empty($p['isCOD'])): ?><span class="badge-cod">COD</span><?php endif; ?>
      </div>
      <div class="product-detail-price">
        <span class="price-main">BDT <?php echo number_format($ep);?></span>
        <?php if($p['originalPrice']>$p['price']): ?><span class="price-old">BDT <?php echo number_format($p['originalPrice']);?></span><?php endif; ?>
        <?php if($p['discount']>0): ?><div class="price-save">You save BDT <?php echo number_format($p['originalPrice']-$ep);?> (<?php echo $p['discount'];?>% off)</div><?php endif; ?>
      </div>
      <?php if(!empty($p['isFlashSale'])&&isset($p['flashSaleStock'])): ?>
      <div style="background:#FFF7ED;border:1.5px solid #FDBA74;border-radius:10px;padding:12px 16px;margin:16px 0;">
        <div style="color:#D97706;font-weight:700;font-size:14px;">⚡ Flash Sale ends in: <span id="pdH">00</span>:<span id="pdM">00</span>:<span id="pdS">00</span></div>
        <div style="background:#E2E8F0;border-radius:3px;height:5px;margin-top:8px;"><div style="background:linear-gradient(90deg,#EF4444,#F97316);height:100%;border-radius:3px;width:<?php echo round(($p['flashSaleSold']/$p['flashSaleStock'])*100);?>%;"></div></div>
        <div style="font-size:12px;color:#64748B;margin-top:4px;"><?php echo $p['flashSaleSold'];?> sold of <?php echo $p['flashSaleStock'];?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($p['colors'])): ?>
      <div style="margin:16px 0;"><div style="font-weight:600;font-size:14px;margin-bottom:10px;">Color:</div>
        <div class="color-picker"><?php foreach($p['colors'] as $i=>$color): ?><div class="color-swatch <?php echo $i===0?'active':'';?>" style="background:<?php echo $color;?>;" onclick="this.parentNode.querySelectorAll('.color-swatch').forEach(s=>s.classList.remove('active'));this.classList.add('active');"></div><?php endforeach; ?></div>
      </div>
      <?php endif; ?>
      <div style="margin:16px 0;"><div style="font-weight:600;font-size:14px;margin-bottom:10px;">Quantity:</div>
        <div class="qty-selector">
          <button class="qty-btn" onclick="changeQty(-1)">−</button>
          <span class="qty-num" id="qtyNum">1</span>
          <button class="qty-btn" onclick="changeQty(1)">+</button>
          <span style="color:#94A3B8;font-size:13px;">Only <?php echo $p['stock'];?> left</span>
        </div>
      </div>
      <div class="action-btns">
        <button class="btn-primary" onclick="addToCartDetail()" style="flex:1;justify-content:center;">🛒 Add to Cart</button>
        <button class="wl-btn btn-secondary" data-id="<?php echo $p['id'];?>" data-name="<?php echo htmlspecialchars($p['name']);?>">🤍</button>
        <a href="cart.php" class="btn-secondary" onclick="addToCartDetail()">Buy Now</a>
      </div>
      <div class="product-trust-badges" style="margin-top:20px;">
        <div class="trust-badge">🛡 Genuine</div>
        <div class="trust-badge">🚚 <?php echo $p['deliveryDays'];?>-day Delivery</div>
        <div class="trust-badge">↺ 7-Day Return</div>
        <?php if(!empty($p['isCOD'])): ?><div class="trust-badge">💵 COD</div><?php endif; ?>
      </div>
      <div class="product-tabs" style="margin-top:28px;">
        <div class="product-tab active" onclick="switchTab(this,'tab-desc')">Description</div>
        <div class="product-tab" onclick="switchTab(this,'tab-specs')">Specifications</div>
        <div class="product-tab" onclick="switchTab(this,'tab-reviews')">Reviews</div>
      </div>
      <div id="tab-desc" class="product-tab-content active"><p style="color:#374151;font-size:15px;line-height:1.8;"><?php echo htmlspecialchars($p['description']);?></p></div>
      <div id="tab-specs" class="product-tab-content"><table class="specs-table"><?php foreach($p['specs'] as $k=>$v): ?><tr><td><?php echo htmlspecialchars($k);?></td><td><?php echo htmlspecialchars($v);?></td></tr><?php endforeach; ?></table></div>
      <div id="tab-reviews" class="product-tab-content">
        <div style="display:flex;align-items:center;gap:20px;margin-bottom:20px;"><div style="text-align:center;"><div style="font-size:56px;font-weight:900;"><?php echo $p['rating'];?></div><div class="stars" style="font-size:20px;"><?php echo str_repeat('★',floor($p['rating']));?></div><div style="color:#64748B;font-size:13px;"><?php echo number_format($p['reviewCount']);?> reviews</div></div></div>
        <?php foreach([['name'=>'Rezaul K.','rating'=>5,'date'=>'2024-01-15','text'=>'Absolutely love this product!'],['name'=>'Sana H.','rating'=>5,'date'=>'2024-01-12','text'=>'Genuine product, fast delivery. Very satisfied.'],['name'=>'Bilal K.','rating'=>4,'date'=>'2024-01-08','text'=>'Great value for money. Highly recommend!']] as $rev): ?>
        <div style="border-bottom:1px solid #F1F5F9;padding:16px 0;"><div style="display:flex;justify-content:space-between;"><div style="font-weight:700;"><?php echo $rev['name'];?></div><span style="color:#94A3B8;font-size:12px;"><?php echo $rev['date'];?></span></div><div class="stars" style="font-size:14px;margin:4px 0;"><?php echo str_repeat('★',$rev['rating']);?></div><p style="color:#374151;font-size:14px;"><?php echo $rev['text'];?></p></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php if(!empty($related)): ?>
  <div style="padding:40px 0;">
    <div class="section-title"><h2>Related Products</h2><a href="products.php?category=<?php echo $p['category'];?>">View All →</a></div>
    <div class="products-grid"><?php foreach($related as $p): ?><?php include 'includes/product_card.php'; ?><?php endforeach; ?></div>
  </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
<script>
let qty=1;
const productId='<?php echo $p['id'];?>',productName='<?php echo addslashes($p['name']);?>',productPrice=<?php echo $ep;?>,productImg='<?php echo $p['images'][0];?>',productBrand='<?php echo addslashes($p['brand']);?>';
function changeQty(d){qty=Math.max(1,qty+d);document.getElementById('qtyNum').textContent=qty;}
function switchImg(src,thumb){document.getElementById('mainImg').src=src;document.querySelectorAll('.thumb').forEach(t=>t.classList.remove('active'));thumb.classList.add('active');}
function switchTab(el,id){document.querySelectorAll('.product-tab').forEach(t=>t.classList.remove('active'));document.querySelectorAll('.product-tab-content').forEach(c=>c.classList.remove('active'));el.classList.add('active');document.getElementById(id).classList.add('active');}
function addToCartDetail(){for(let i=0;i<qty;i++)addToCart(productId,productName,productPrice,productImg,productBrand);}
addRecentlyViewed(productId);
<?php if(!empty($p['isFlashSale'])): ?>(function(){const end=<?php echo $FLASH_SALE_END;?>;function u(){const d=Math.max(0,end-Date.now());const h=Math.floor(d/3600000),m=Math.floor((d%3600000)/60000),s=Math.floor((d%60000)/1000);const pad=n=>String(n).padStart(2,'0');const hEl=document.getElementById('pdH'),mEl=document.getElementById('pdM'),sEl=document.getElementById('pdS');if(hEl)hEl.textContent=pad(h);if(mEl)mEl.textContent=pad(m);if(sEl)sEl.textContent=pad(s);}u();setInterval(u,1000);})();<?php endif; ?>
</script>
