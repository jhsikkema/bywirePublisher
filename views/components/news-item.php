<?php
    preg_match('/<img.*?src="(.+?)"/i', $article->article_content, $matches);
    $img_url = ($matches) ? str_replace("\\\\", "", $matches[1]) : "";
    if (isset($article->img_url)) {
        $img_url = $article->img_url;

    }
    $slug    = $article->slug;
    $slug    = preg_replace("[\.]", "", $slug);
    $slug    = preg_replace("[/;':]", "", $slug);
    $slug    = preg_replace("/-+/", "-", $slug);
    $url     = "https://bywire.news/articles/".$slug;
    $command = 'window.open("'.$url.'", "", "width=400,height=200,menubar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes")';
    $summary = $article->article_content;
    //$summary = preg_replace("/^(.*?[.].*?[.]).*$/", "\\1", $summary);
    $summary = substr($summary, 0, 410);
    $summary = preg_replace("/ [^ ]*$/", "", $summary);
    $summary = $summary."...";
?>

<div class="col-4 pb-0 d-flex align-items-stretch news--item">
    <div class="card py-0 px-0 w-100">
        <?php if($img_url): ?>
            <img class="card-img-top" src="<?php print str_replace('\\', '', $img_url); ?>" alt="<?php echo $article->title; ?>">
        <?php else: ?>
            <img class="card-img-top" src="<?php print BYWIRE__PLUGIN_URL; ?>/assets/image/no-image.png" alt="<?php echo $article->title; ?> - No image">
        <?php endif; ?>
        <div class="card-body">
            <h5 class="card-title"><?php echo $article->title; ?></h5>
            <p class="card-text"><?php print strip_tags($summary); ?></p>
        </div>
        <div class="card-footer">
            <div class="row no-gutters">
                <div class="col-6 pr-1">
                    <a href="<?php echo $url;?>" target="_blank" class="btn btn-dark w-100">Read Story</a>
                </div>
                <div class="col-6 pl-1">
                    <a href="http://twitter.com/share?text=<?php print urlencode($article->title); ?>&url=<?php echo $url;?>" target="_blank" class="btn btn-dark w-100"><i class="icon icon-twitter"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
