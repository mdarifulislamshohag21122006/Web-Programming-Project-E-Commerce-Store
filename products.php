<?php
require_once 'includes/products_data.php';
$q=trim($_GET['q']??''); $category=$_GET['category']??''; $brand=$_GET['brand']??'';
$flashsale=!empty($_GET['flashsale']); $cod=!empty($_GET['cod']); $sort=$_GET['sort']??'default';
$minPrice=(int)($_GET['min']??0); $maxPrice=(int)($_GET['max']??999999);
$filtered=$products;
if($q) $filtered=array_filter($filtered,fn($p)=>stripos($p['name'].' '.$p['brand'].' '.$p['category'],$q)!==false);
if($category) $filtered=array_filter($filtered,fn($p)=>$p['category']===$category);
if($brand) $filtered=array_filter($filtered,fn($p)=>$p['brand']===$brand);
if($flashsale) $filtered=array_filter($filtered,fn($p)=>!empty($p['isFlashSale']));
if($cod) $filtered=array_filter($filtered,fn($p)=>!empty($p['isCOD']));
if($minPrice>0||$maxPrice<999999) $filtered=array_filter($filtered,fn($p)=>$p['price']>=$minPrice&&$p['price']<=$maxPrice);
$filtered=array_values($filtered);
switch($sort){case 'price_asc':usort($filtered,fn($a,$b)=>$a['price']-$b['price']);break;case 'price_desc':usort($filtered,fn($a,$b)=>$b['price']-$a['price']);break;case 'rating':usort($filtered,fn($a,$b)=>$b['rating']<=>$a['rating']);break;case 'discount':usort($filtered,fn($a,$b)=>$b['discount']-$a['discount']);break;}
$pageTitle=$q?'Search: '.htmlspecialchars($q):($category?ucfirst($category):'All Products');
?>
<?php include 'includes/header.php'; ?><?php include 'includes/navbar.php'; ?>
<div class="page-hero"><div class="container"><h1><?php echo $pageTitle;?></h1><p><?php echo count($filtered);?> products found</p></div></div>
<div class="container"><div class="products-layout">
<div class="sidebar">
  <div class="filter-section">
    <div class="filter-title">🔍 Search</div>
    <form method="GET"><input type="text" name="q" value="<?php echo htmlspecialchars($q);?>" placeholder="Search..." class="form-input" style="margin-bottom:10px;"><input type="hidden" name="category" value="<?php echo htmlspecialchars($category);?>"><button type="submit" class="btn-primary" style="width:100%;justify-content:center;">Search</button></form>
  </div>
  <div class="filter-section">
    <div class="filter-title">📂 Category</div>
    <?php foreach($CATEGORIES as $cat): ?>
    <div class="filter-item"><input type="radio" id="cat_<?php echo $cat['id'];?>" <?php echo $category===$cat['id']?'checked':'';?> onchange="applyFilter('category','<?php echo $cat['id'];?>')"><label for="cat_<?php echo $cat['id'];?>"><?php echo $cat['icon'].' '.$cat['name'];?></label></div>
    <?php endforeach; ?>
    <?php if($category): ?><button onclick="applyFilter('category','')" style="color:#F97316;font-size:13px;margin-top:6px;">✕ Clear</button><?php endif; ?>
  </div>
  <div class="filter-section">
    <div class="filter-title">🏷 Brand</div>
    <?php foreach(array_slice($BRANDS,0,10) as $b): ?>
    <div class="filter-item"><input type="radio" id="b_<?php echo $b;?>" <?php echo $brand===$b?'checked':'';?> onchange="applyFilter('brand','<?php echo $b;?>')"><label for="b_<?php echo $b;?>"><?php echo $b;?></label></div>
    <?php endforeach; ?>
    <?php if($brand): ?><button onclick="applyFilter('brand','')" style="color:#F97316;font-size:13px;margin-top:6px;">✕ Clear</button><?php endif; ?>
  </div>
  <div class="filter-section">
    <div class="filter-title">💰 Price Range</div>
    <form method="GET"><input type="hidden" name="category" value="<?php echo htmlspecialchars($category);?>"><input type="hidden" name="brand" value="<?php echo htmlspecialchars($brand);?>"><input type="hidden" name="q" value="<?php echo htmlspecialchars($q);?>"><div class="price-range"><input type="number" name="min" value="<?php echo $minPrice?:''?>" placeholder="Min"><span>–</span><input type="number" name="max" value="<?php echo $maxPrice<999999?$maxPrice:''?>" placeholder="Max"></div><button type="submit" class="btn-secondary" style="width:100%;justify-content:center;margin-top:10px;">Apply</button></form>
  </div>
  <div class="filter-section">
    <div class="filter-title">⚡ Quick Filters</div>
    <div class="filter-item"><input type="checkbox" id="flashCheck" <?php echo $flashsale?'checked':'';?> onchange="applyFilter('flashsale',this.checked?'1':'')"><label for="flashCheck">⚡ Flash Sale</label></div>
    <div class="filter-item"><input type="checkbox" id="codCheck" <?php echo $cod?'checked':'';?> onchange="applyFilter('cod',this.checked?'1':'')"><label for="codCheck">💵 Cash on Delivery</label></div>
  </div>
</div>
<div>
  <div class="products-main-header">
    <span class="results-count"><?php echo count($filtered);?> results<?php echo $q?' for "'.htmlspecialchars($q).'"':'';?></span>
    <select class="sort-select" onchange="applyFilter('sort',this.value)">
      <option value="default" <?php echo $sort==='default'?'selected':'';?>>Default</option>
      <option value="price_asc" <?php echo $sort==='price_asc'?'selected':'';?>>Price: Low to High</option>
      <option value="price_desc" <?php echo $sort==='price_desc'?'selected':'';?>>Price: High to Low</option>
      <option value="rating" <?php echo $sort==='rating'?'selected':'';?>>Top Rated</option>
      <option value="discount" <?php echo $sort==='discount'?'selected':'';?>>Biggest Discount</option>
    </select>
  </div>
  <?php if(!empty($filtered)): ?>
  <div class="products-grid"><?php foreach($filtered as $p): ?><?php include 'includes/product_card.php'; ?><?php endforeach; ?></div>
  <?php else: ?>
  <div class="empty-cart"><div class="empty-icon">🔍</div><div class="empty-title">No products found</div><p class="empty-desc">Try adjusting your filters.</p><a href="products.php" class="btn-primary">Clear Filters</a></div>
  <?php endif; ?>
</div>
</div></div>
<?php include 'includes/footer.php'; ?>
<script>function applyFilter(key,value){const url=new URL(window.location.href);if(value===''||value===null){url.searchParams.delete(key);}else{url.searchParams.set(key,value);}window.location.href=url.toString();}</script>
