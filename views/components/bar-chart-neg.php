<?php
$chart_id = rand();
?>

<div class="row">
    <div class="col-12 mt-5" style="height: 300px;">
        <canvas data-labels='<?php print json_encode(array_keys($bar_chart_data)); ?>' data-values='<?php print json_encode(array_values($bar_chart_data)); ?>' class="chart chartjs-chart" id="neg-chart-<?php print $chart_id; ?>" style="width: 100%;" height="100%"></canvas>
    </div>
</div>
