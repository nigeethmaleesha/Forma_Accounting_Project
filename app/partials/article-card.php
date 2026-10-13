<a class="article-card" data-reveal href="/insights/<?= e($item['slug']) ?>/">
 <span class="article-cover"><img src="/assets/images/<?= e($item['image']) ?>" alt="Business and advisory editorial photograph" loading="lazy" decoding="async"><span class="article-cover-arrow"><?= arrow_icon() ?></span></span>
 <span class="article-card-body"><span class="article-cat"><?= e(strtoupper($item['category'])) ?> · EDITORIAL SAMPLE</span><span class="article-name"><?= e($item['title']) ?></span><span class="article-desc"><?= e($item['summary']) ?></span><span class="article-read">Read insight <?= arrow_icon() ?></span></span>
</a>
