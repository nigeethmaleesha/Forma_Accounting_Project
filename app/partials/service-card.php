<a class="service-card" data-reveal href="/services/<?= e($item['slug']) ?>/">
  <span class="service-top"><span class="service-icon" aria-hidden="true">
    <?php
    $icons = [
      '<rect x="3" y="10" width="4" height="10"/><rect x="10" y="6" width="4" height="14"/><rect x="17" y="3" width="4" height="17"/>',
      '<path d="M6 3h9l4 4v14H6zM15 3v5h4M9 12h7M9 16h5"/>',
      '<rect x="3" y="6" width="18" height="14" rx="2"/><path d="M3 11h18M8 3v6M16 3v6M7 15h4"/>',
      '<path d="M3 17l6-7 5 4 7-9M16 5h5v5M3 21h18"/>',
      '<circle cx="12" cy="12" r="9"/><path d="M12 3v9h9M5 18l4-5"/>',
      '<path d="M12 2l8 4v6c0 5-3.7 8.6-8 10-4.3-1.4-8-5-8-10V6zM9 12l2 2 4-4"/>',
    ];
    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' . $icons[$i % count($icons)] . '</svg>';
    ?>
  </span><span class="service-index"><?= e(str_pad((string)($i+1),2,'0',STR_PAD_LEFT)) ?></span></span>
  <span class="service-title"><?= e($item['title']) ?></span>
  <span class="service-description"><?= e($item['summary']) ?></span>
  <span class="service-bottom">Explore service <span><?= arrow_icon() ?></span></span>
</a>
