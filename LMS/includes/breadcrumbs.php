<?php
// Usage: Place this include after setting $breadcrumbs = [ 'Page Name' => 'url', ... ];
if (!isset($breadcrumbs) || !is_array($breadcrumbs) || count($breadcrumbs) === 0) return;
?>
<nav aria-label="Breadcrumb" class="bch-breadcrumb bg-white/90 backdrop-blur border border-blue-100 shadow-sm rounded-xl px-4 py-3 mb-6 flex items-center overflow-x-auto" tabindex="0">
  <ol class="flex flex-wrap items-center text-sm text-gray-600 gap-x-2 gap-y-1 whitespace-nowrap w-full" itemscope itemtype="https://schema.org/BreadcrumbList">
    <?php $i = 1; $last = count($breadcrumbs); foreach ($breadcrumbs as $label => $url): ?>
      <li class="inline-flex items-center" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
        <?php if ($url && $i < $last): ?>
          <a href="<?= htmlspecialchars($url) ?>" class="hover:text-primary font-semibold focus:outline-none focus:underline transition-colors px-1" itemprop="item">
            <span itemprop="name"><?= htmlspecialchars($label) ?></span>
          </a>
          <meta itemprop="position" content="<?= $i ?>" />
          <svg class="mx-2 h-4 w-4 text-blue-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
        <?php else: ?>
          <span class="text-primary font-bold px-1" itemprop="name"><?= htmlspecialchars($label) ?></span>
          <meta itemprop="position" content="<?= $i ?>" />
        <?php endif; ?>
      </li>
    <?php $i++; endforeach; ?>
  </ol>
</nav>
