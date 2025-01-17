<?php
function getBreadcrumb() {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = array_filter(explode('/', $path));
    
    $breadcrumbs = [
        ['title' => 'Home', 'url' => '/']
    ];
    
    if (!empty($segments)) {
        $currentPath = '';
        foreach ($segments as $segment) {
            $currentPath .= '/' . $segment;
            $title = ucwords(str_replace(['-', '.php'], [' ', ''], $segment));
            $breadcrumbs[] = [
                'title' => $title,
                'url' => $currentPath
            ];
        }
    }
    
    return $breadcrumbs;
}
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <?php 
            $breadcrumbs = getBreadcrumb();
            foreach ($breadcrumbs as $index => $crumb): 
                $isLast = $index === count($breadcrumbs) - 1;
            ?>
                <li class="breadcrumb-item <?php echo $isLast ? 'active' : ''; ?>">
                    <?php if (!$isLast): ?>
                        <a href="<?php echo htmlspecialchars($crumb['url']); ?>">
                            <?php echo htmlspecialchars($crumb['title']); ?>
                        </a>
                    <?php else: ?>
                        <?php echo htmlspecialchars($crumb['title']); ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div> 