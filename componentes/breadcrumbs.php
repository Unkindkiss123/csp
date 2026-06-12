<?php
if (!isset($breadcrumb) || !is_array($breadcrumb) || count($breadcrumb) === 0) {
    return;
}
?>
<nav aria-label="breadcrumb" class="mt-2">
  <ol class="breadcrumb <?=(isset($isDarkHero)&&$isDarkHero)?'csp-breadcrumb':''?>">
    <?php foreach ($breadcrumb as $i => $item):
      $label = htmlspecialchars($item['label'] ?? '');
      $href  = $item['href'] ?? null;
      $isLast = ($i === array_key_last($breadcrumb));
    ?>
      <?php if ($href && !$isLast): ?>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars($url_for($href)) ?>"><?= $label ?></a></li>
      <?php else: ?>
        <li class="breadcrumb-item active" aria-current="page"><?= $label ?></li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ol>
  </nav>
