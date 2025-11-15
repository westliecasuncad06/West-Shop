<?php
// Shared storefront view used by buyer storefront and seller preview.
$storeViewMode = $storeViewMode ?? 'buyer';
$isBuyer = $isBuyer ?? false;
$isFollowing = $isFollowing ?? false;
$rating = $rating ?? ['avg' => 0, 'cnt' => 0];
$followers = $followers ?? 0;
$products = $products ?? [];
$activeCoupons = $activeCoupons ?? [];
$storeReviews = $storeReviews ?? [];
$existingStoreReview = $existingStoreReview ?? null;
$storeImagePlaceholder = base_url('assets/images/products/placeholder.png');
$resolveStoreImage = function (?string $path) use ($storeImagePlaceholder) {
  if (!$path) {
    return $storeImagePlaceholder;
  }
  if (preg_match('/^https?:\/\//i', $path)) {
    return $path;
  }
  return base_url(ltrim($path, '/'));
};
$bannerImage = $resolveStoreImage($store['banner'] ?? null);
$logoImage = $resolveStoreImage($store['logo'] ?? null);
$productCount = $productCount ?? count($products);
$sellerRefId = $seller_id ?? ($store['seller_id'] ?? null);
$contactUrl = $sellerRefId ? base_url('buyer/chat.php?to=' . (int)$sellerRefId) : '#';
$showBuyerActions = ($storeViewMode === 'buyer' && $isBuyer);
?>

<?php if ($storeViewMode === 'seller_preview'): ?>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Buyer View Preview</h4>
    <a class="btn btn-outline-secondary" href="<?php echo e($previewExitUrl ?? base_url('seller/index.php')); ?>">Exit Preview</a>
  </div>
<?php endif; ?>

<!-- Store Hero -->
<section class="store-hero shadow-sm mb-4">
  <div class="store-hero__media">
    <img src="<?php echo e($bannerImage); ?>" alt="Store banner" class="store-hero__banner">
    <span class="store-hero__overlay"></span>
  </div>
  <div class="store-hero__content p-4 p-md-5">
    <div class="d-flex flex-column flex-md-row gap-4 align-items-start align-items-md-center">
      <div class="store-hero__logo-wrapper">
        <img src="<?php echo e($logoImage); ?>" alt="Store logo" class="store-hero__logo">
      </div>
      <div class="flex-grow-1">
        <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-start align-items-md-center">
          <div>
            <h2 class="fw-bold mb-1"><?php echo e($store['shop_name'] ?? (($store['seller_name'] ?? 'Seller') . ' Shop')); ?></h2>
            <div class="text-white-50 small">by <?php echo e($store['seller_name'] ?? 'Seller'); ?></div>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <?php if ($showBuyerActions): ?>
              <a class="btn btn-light btn-sm" href="<?php echo e($contactUrl); ?>">Contact Seller</a>
              <form method="post" action="<?php echo e($storeActionUrl ?? ''); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="<?php echo $isFollowing ? 'unfollow_store' : 'follow_store'; ?>">
                <button class="btn btn-sm btn-<?php echo $isFollowing ? 'secondary' : 'primary'; ?> text-white">
                  <?php echo $isFollowing ? 'Unfollow Store' : 'Follow Store'; ?>
                </button>
              </form>
            <?php else: ?>
              <button class="btn btn-light btn-sm" type="button" disabled>Contact Seller</button>
              <button class="btn btn-sm btn-secondary text-white" type="button" disabled>Follow Store</button>
            <?php endif; ?>
          </div>
        </div>
        <ul class="store-hero__meta list-unstyled d-flex flex-wrap gap-4 mt-3 mb-0">
          <li>
            <span class="store-hero__meta-label">Rating</span>
            <strong><?php echo number_format((float)($rating['avg'] ?? 0), 1); ?></strong>
            <span class="text-white-50">(<?php echo (int)($rating['cnt'] ?? 0); ?>)</span>
          </li>
          <li>
            <span class="store-hero__meta-label">Followers</span>
            <strong><?php echo (int)$followers; ?></strong>
          </li>
          <li>
            <span class="store-hero__meta-label">Products</span>
            <strong><?php echo (int)$productCount; ?></strong>
          </li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- Store Insights -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="insight-card shadow-sm">
      <span class="insight-card__icon">⭐</span>
      <div>
        <p class="text-uppercase small text-muted mb-1">Customer Trust</p>
        <h4 class="mb-0"><?php echo number_format((float)($rating['avg'] ?? 0), 1); ?> / 5</h4>
        <small class="text-muted"><?php echo (int)($rating['cnt'] ?? 0); ?> verified reviews</small>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="insight-card shadow-sm">
      <span class="insight-card__icon">👥</span>
      <div>
        <p class="text-uppercase small text-muted mb-1">Community</p>
        <h4 class="mb-0"><?php echo (int)$followers; ?> followers</h4>
        <small class="text-muted">Growing loyal shoppers</small>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="insight-card shadow-sm">
      <span class="insight-card__icon">🛒</span>
      <div>
        <p class="text-uppercase small text-muted mb-1">Catalog</p>
        <h4 class="mb-0"><?php echo (int)$productCount; ?> products</h4>
        <small class="text-muted">Fresh arrivals weekly</small>
      </div>
    </div>
  </div>
</div>

<!-- Active Coupons Section -->
<?php if (count($activeCoupons) > 0): ?>
<section class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
    <div>
      <p class="text-uppercase small text-muted mb-0">Limited Offers</p>
      <h5 class="fw-bold mb-0">Available Coupons</h5>
    </div>
    <span class="badge bg-primary-subtle text-primary">Save more today</span>
  </div>
  <div class="card-body">
    <div class="coupon-grid">
      <?php foreach ($activeCoupons as $coupon): ?>
        <div class="coupon-card">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <p class="text-uppercase small text-muted mb-1">Code</p>
              <h5 class="mb-0"><?php echo e($coupon['code']); ?></h5>
            </div>
            <span class="coupon-card__badge">
              <?php if ($coupon['type'] === 'percent'): ?>
                <?php echo e($coupon['value']); ?>% OFF
              <?php else: ?>
                $<?php echo e($coupon['value']); ?> OFF
              <?php endif; ?>
            </span>
          </div>
          <p class="small text-muted mb-1">Use at checkout to redeem.</p>
          <?php if (!empty($coupon['expires_at'])): ?>
            <p class="small text-success mb-1">Valid until <?php echo e(date('M j, Y', strtotime($coupon['expires_at']))); ?></p>
          <?php else: ?>
            <p class="small text-success mb-1">No expiry date</p>
          <?php endif; ?>
          <?php if (!empty($coupon['max_uses'])): ?>
            <p class="small text-muted mb-0"><?php echo ($coupon['max_uses'] - ($coupon['used_count'] ?? 0)); ?> redemptions left</p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<div class="row g-4 align-items-start">
  <!-- Main Content -->
  <div class="col-lg-8">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
      <div>
        <p class="text-uppercase small text-muted mb-1">Shop the collection</p>
        <h5 class="fw-bold mb-0">Products</h5>
      </div>
      <div class="product-legend text-muted small">
        Curated items from <?php echo e($store['shop_name'] ?? 'this store'); ?>
      </div>
    </div>

    <div class="row g-3">
      <?php foreach ($products as $prod): ?>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
          <a href="<?php echo e(base_url('public/product.php?id=' . (int)$prod['product_id'])); ?>" class="text-decoration-none text-reset d-block h-100">
            <div class="card border-0 shadow-sm h-100 product-card">
              <div class="product-card__image">
                <img src="<?php echo e($prod['image'] ?: base_url('assets/images/products/placeholder.png')); ?>" class="img-fluid" alt="<?php echo e($prod['name']); ?>">
              </div>
              <div class="card-body">
                <p class="text-uppercase small text-muted mb-1">Featured item</p>
                <h6 class="fw-semibold mb-2 text-truncate"><?php echo e($prod['name']); ?></h6>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-bold text-primary">$<?php echo number_format($prod['price'], 2); ?></span>
                  <span class="badge bg-light text-muted">View details</span>
                </div>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
      <?php if (!$products): ?>
        <div class="col-12">
          <div class="empty-state text-center p-5">
            <p class="lead mb-1">No products yet</p>
            <p class="text-muted mb-0">Follow the store to get notified when new items arrive.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Store Snapshot</h6>
        <p class="text-muted small mb-3"><?php echo e($store['description'] ?? 'The shop where you can find happiness'); ?></p>
        <ul class="list-unstyled store-highlights mb-0">
          <li>
            <span class="store-highlights__label">Shipping</span>
            <p class="mb-0 small"><?php echo e($store['shipping_policy'] ?? 'N/A'); ?></p>
          </li>
          <li>
            <span class="store-highlights__label">Returns</span>
            <p class="mb-0 small"><?php echo e($store['return_policy'] ?? 'N/A'); ?></p>
          </li>
        </ul>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Stay in touch</h6>
        <?php if ($showBuyerActions): ?>
          <a href="<?php echo e($contactUrl); ?>" class="btn btn-outline-primary w-100 mb-3">Message Seller</a>
        <?php else: ?>
          <button class="btn btn-outline-primary w-100 mb-3" type="button" disabled>Message Seller</button>
        <?php endif; ?>
        <div class="d-flex gap-3 justify-content-center text-muted small">
          <span>Secure payments</span>
          <span>Fast support</span>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Rate this store</h6>
        <?php if ($storeViewMode === 'buyer' && $isBuyer): ?>
          <form method="post" action="<?php echo e($storeActionUrl ?? ''); ?>" class="rating-form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="rate_store">
            <?php if ($existingStoreReview): ?>
              <div class="text-muted small mb-1">Updating your previous <?php echo (int)$existingStoreReview['rating']; ?>/5 rating</div>
            <?php endif; ?>
            <div class="star-rating mb-2">
              <?php for($i=5;$i>=1;$i--): ?>
                <input type="radio" id="rate_<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo (($existingStoreReview['rating'] ?? 5) == $i) ? 'checked' : ''; ?>>
                <label for="rate_<?php echo $i; ?>" title="Rate <?php echo $i; ?> stars"></label>
              <?php endfor; ?>
            </div>
            <div class="mb-3">
              <textarea name="comment" class="form-control form-control-sm" rows="3" placeholder="Share your experience"><?php echo e($existingStoreReview['comment'] ?? ''); ?></textarea>
            </div>
            <button class="btn btn-primary btn-sm w-100"><?php echo $existingStoreReview ? 'Update rating' : 'Submit rating'; ?></button>
          </form>
        <?php elseif ($storeViewMode === 'buyer'): ?>
          <div class="small text-muted">Login as buyer to rate this store.</div>
        <?php else: ?>
          <div class="small text-muted">Rating form hidden in preview.</div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (count($activeCoupons) > 0 && count($activeCoupons) <= 2): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h6 class="fw-bold mb-3">Quick Coupons</h6>
          <?php foreach ($activeCoupons as $coupon): ?>
            <div class="mb-3 p-3 rounded bg-light">
              <div class="fw-bold text-success"><?php echo e($coupon['code']); ?></div>
              <div class="small text-muted">
                <?php if ($coupon['type'] === 'percent'): ?>
                  Save <?php echo e($coupon['value']); ?>%
                <?php else: ?>
                  Save $<?php echo e($coupon['value']); ?>
                <?php endif; ?>
                on your order
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<section class="card border-0 shadow-sm mt-4" id="store-reviews">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div>
        <p class="section-label mb-1">Community voices</p>
        <h5 class="mb-0">Store Reviews</h5>
      </div>
      <span class="badge-chip"><?php echo (int)($rating['cnt'] ?? 0); ?> total ratings</span>
    </div>
    <?php if ($storeReviews): ?>
      <div class="row g-3">
        <?php foreach(array_slice($storeReviews, 0, 6) as $rev): ?>
          <div class="col-md-6">
            <div class="review-card h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <strong><?php echo e($rev['buyer_name'] ?? 'Buyer'); ?></strong>
                  <div class="text-muted small">Rated <?php echo (int)$rev['rating']; ?>/5</div>
                </div>
                <span class="text-muted small"><?php echo date('M d, Y', strtotime($rev['updated_at'] ?? $rev['created_at'])); ?></span>
              </div>
              <p class="mt-3 mb-0 small"><?php echo $rev['comment'] ? nl2br(e($rev['comment'])) : '<span class="text-muted">No comment left.</span>'; ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-panel">
        <p class="mb-1 fw-semibold">No store reviews yet</p>
        <p class="text-muted mb-0">Be the first buyer to share your experience.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<style>
.store-hero {
  position: relative;
  border-radius: 1.25rem;
  overflow: hidden;
  background: #0d6efd;
  color: #fff;
}
.store-hero__media {
  position: absolute;
  inset: 0;
}
.store-hero__banner {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.store-hero__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(13,110,253,0.9), rgba(111,66,193,0.85));
}
.store-hero__content {
  position: relative;
  z-index: 2;
}
.store-hero__logo-wrapper {
  width: 120px;
  height: 120px;
  border-radius: 1.25rem;
  background: rgba(255,255,255,0.15);
  padding: 6px;
}
.store-hero__logo {
  width: 100%;
  height: 100%;
  border-radius: 1rem;
  object-fit: cover;
  background: #fff;
}
.store-hero__meta-label {
  display: block;
  font-size: 0.75rem;
  text-transform: uppercase;
  color: rgba(255,255,255,0.7);
}
.insight-card {
  border-radius: 1rem;
  background: #fff;
  padding: 1.25rem;
  display: flex;
  gap: 1rem;
  align-items: center;
}
.insight-card__icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: rgba(13,110,253,0.1);
  display: grid;
  place-items: center;
  font-size: 1.5rem;
}
.coupon-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1rem;
}
.coupon-card {
  border: 1px dashed rgba(13,110,253,0.3);
  border-radius: 1rem;
  padding: 1.25rem;
  background: rgba(13,110,253,0.02);
}
.coupon-card__badge {
  padding: 0.35rem 0.65rem;
  border-radius: 999px;
  background: rgba(25,135,84,0.15);
  color: #198754;
  font-weight: 600;
}
.product-card {
  border-radius: 1rem;
}
.product-card__image {
  height: 200px;
  border-radius: 1rem 1rem 0 0;
  overflow: hidden;
}
.product-card__image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.empty-state {
  border-radius: 1rem;
  background: #fff;
}
.store-highlights li + li {
  margin-top: 1rem;
}
.store-highlights__label {
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #6c757d;
  font-weight: 600;
}
@media (max-width: 767px) {
  .store-hero__logo-wrapper {
    width: 96px;
    height: 96px;
  }
  .product-card__image {
    height: 160px;
  }
}
.star-rating {
  display: inline-flex;
  flex-direction: row-reverse;
  justify-content: flex-end;
  gap: 0.35rem;
}
.star-rating input { display: none; }
.star-rating label {
  width: 22px;
  height: 22px;
  background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%23d1d5db" stroke-width="2"><polygon points="12 2 15 9 23 9 17 14 19 22 12 18 5 22 7 14 1 9 9 9"/></svg>') center/contain no-repeat;
  cursor: pointer;
}
.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
  background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%232563eb"><polygon points="12 2 15 9 23 9 17 14 19 22 12 18 5 22 7 14 1 9 9 9"/></svg>');
}
</style>
