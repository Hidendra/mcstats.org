<?php
$current = 'december-2012';

// Analytics
$visits = 14589;
$unique_visitors = 8647;
$new_visitors = 0;
$pageviews = 35807;
$pages_visit = 2.45;
$avg_visit = '00:03:26';

?>
<div class="widget-box">
    <div class="widget-title"><span class="icon"><i class="icon-signal"></i></span><h5>Site Analytics</h5></div>
    <div class="widget-content">
        <div class="row-fluid">
            <div class="span4">
                <ul class="site-stats">
                    <li><i class="icon-download-alt"></i> <strong><?php echo number_format($visits); ?></strong>
                        <small>Visits</small>
                    </li>
                    <li><i class="icon-user"></i> <strong><?php echo number_format($unique_visitors); ?></strong>
                        <small>Unique Visitors</small>
                    </li>
                    <li><i class="icon-arrow-right"></i> <strong><?php echo number_format($new_visitors); ?></strong>
                        <small>New Visitors (over last month)</small>
                    </li>
                    <li class="divider"></li>
                    <li><i class="icon-signal"></i> <strong><?php echo number_format($pageviews); ?></strong>
                        <small>Pageviews</small>
                    </li>
                    <li><i class="icon-repeat"></i> <strong><?php echo number_format($pages_visit, 2); ?></strong>
                        <small>Pages / visit</small>
                    </li>
                    <li><i class="icon-time"></i> <strong><?php echo $avg_visit; ?></strong>
                        <small>Avg. Visit Duration</small>
                    </li>
                </ul>
            </div>
            <div class="span8">
                <a href="https://d2jz01fyat1phn.cloudfront.net/reports/<?php echo $current; ?>/analytics_monthly.png"
                   target="_blank"><img
                        src="https://d2jz01fyat1phn.cloudfront.net/reports/<?php echo $current; ?>/analytics_monthly.png"/></a>
            </div>
        </div>
    </div>
</div>

<div class="widget-box">
    <div class="widget-title"><span class="icon"><i class="icon-signal"></i></span><h5>Historical Analytics</h5></div>
    <div class="widget-content">
        <div class="row-fluid">
            <div class="span12" style="text-align: center;">
                <a href="https://d2jz01fyat1phn.cloudfront.net/reports/<?php echo $current; ?>/analytics_historical.png"
                   target="_blank"><img
                        src="https://d2jz01fyat1phn.cloudfront.net/reports/<?php echo $current; ?>/analytics_historical.png"/></a>
            </div>
        </div>
    </div>
</div>

<div class="widget-box">
    <div class="widget-title"><span class="icon"><i class="icon-signal"></i></span><h5>nginx requests per second</h5>
    </div>
    <div class="widget-content">
        <div class="row-fluid">
            <div class="span12" style="text-align: center;">
                <a href="https://d2jz01fyat1phn.cloudfront.net/reports/<?php echo $current; ?>/nginx_requests.png"
                   target="_blank"><img
                        src="https://d2jz01fyat1phn.cloudfront.net/reports/<?php echo $current; ?>/nginx_requests.png"/></a>
            </div>
        </div>
    </div>
</div>

<div class="widget-box">
    <div class="widget-title"><span class="icon"><i class="icon-signal"></i></span><h5>mysql queries per second</h5>
    </div>
    <div class="widget-content">
        <div class="row-fluid">
            <div class="span12" style="text-align: center;">
                <a href="https://d2jz01fyat1phn.cloudfront.net/reports/<?php echo $current; ?>/mysql_queries.png"
                   target="_blank"><img
                        src="https://d2jz01fyat1phn.cloudfront.net/reports/<?php echo $current; ?>/mysql_queries.png"/></a>
            </div>
        </div>
    </div>
</div>

<div class="widget-box">
    <div class="widget-title"><span class="icon"><i class="icon-signal"></i></span><h5>MySQL Row Counts</h5></div>
    <div class="widget-content">
        <div class="row-fluid">
            <div class="span12" style="text-align: center;">
                <a href="https://d2jz01fyat1phn.cloudfront.net/reports/<?php echo $current; ?>/mysql_rows.png"
                   target="_blank"><img
                        src="https://d2jz01fyat1phn.cloudfront.net/reports/<?php echo $current; ?>/mysql_rows.png"/></a>
            </div>
        </div>
    </div>
</div>

<div class="widget-box">
<div class="widget-title"><span class="icon"><i class="icon-th"></i></span><h5>MySQL Row Counts (raw data)</h5>
</div>
<div class="widget-content nopadding">
<table class="table table-bordered table-striped">
<thead>
<tr>
    <th> Date</th>
    <th> Author</th>
    <th> Graph</th>
    <th> GraphData</th>
    <th> Plugin</th>
    <th> Server</th>
    <th> ServerPlugin</th>
    <th> Versions</th>
</tr>
</thead>
<tbody>
<tr>
    <td>12/01/12</td>
    <td>957</td>
    <td>11744</td>
    <td>105138139</td>
    <td>2217</td>
    <td>4054429</td>
    <td>10626635</td>
    <td>11747</td>
</tr>
<tr>
    <td>12/02/12</td>
    <td>957</td>
    <td>11793</td>
    <td>106446417</td>
    <td>2224</td>
    <td>4074186</td>
    <td>10693256</td>
    <td>11818</td>
</tr>
<tr>
    <td>12/03/12</td>
    <td>967</td>
    <td>11941</td>
    <td>107910022</td>
    <td>2246</td>
    <td>4099820</td>
    <td>10783054</td>
    <td>11896</td>
</tr>
<tr>
    <td>12/04/12</td>
    <td>973</td>
    <td>12005</td>
    <td>109333275</td>
    <td>2256</td>
    <td>4110725</td>
    <td>10821943</td>
    <td>11940</td>
</tr>
<tr>
    <td>12/05/12</td>
    <td>976</td>
    <td>12029</td>
    <td>110757418</td>
    <td>2261</td>
    <td>4122030</td>
    <td>10861601</td>
    <td>12018</td>
</tr>
<tr>
    <td>12/06/12</td>
    <td>982</td>
    <td>12147</td>
    <td>112193866</td>
    <td>2272</td>
    <td>4134051</td>
    <td>10904274</td>
    <td>12080</td>
</tr>
<tr>
    <td>12/07/12</td>
    <td>988</td>
    <td>12202</td>
    <td>113624805</td>
    <td>2281</td>
    <td>4145426</td>
    <td>10944591</td>
    <td>12151</td>
</tr>
<tr>
    <td>12/08/12</td>
    <td>994</td>
    <td>12236</td>
    <td>113981489</td>
    <td>2292</td>
    <td>4155823</td>
    <td>10980484</td>
    <td>12190</td>
</tr>
<tr>
    <td>12/09/12</td>
    <td>1000</td>
    <td>12436</td>
    <td>115027088</td>
    <td>2300</td>
    <td>4178083</td>
    <td>11056230</td>
    <td>12234</td>
</tr>
<tr>
    <td>12/10/12</td>
    <td>1005</td>
    <td>12692</td>
    <td>116499112</td>
    <td>2306</td>
    <td>4196459</td>
    <td>11120525</td>
    <td>12294</td>
</tr>
<tr>
    <td>12/11/12</td>
    <td>1012</td>
    <td>12975</td>
    <td>118080471</td>
    <td>2313</td>
    <td>4208411</td>
    <td>11163502</td>
    <td>12324</td>
</tr>
<tr>
    <td>12/12/12</td>
    <td>1013</td>
    <td>13076</td>
    <td>119750301</td>
    <td>2319</td>
    <td>4220313</td>
    <td>11204650</td>
    <td>12363</td>
</tr>
<tr>
    <td>12/13/12</td>
    <td>1015</td>
    <td>13140</td>
    <td>121469498</td>
    <td>2326</td>
    <td>4232783</td>
    <td>11246966</td>
    <td>12396</td>
</tr>
<tr>
    <td>12/14/12</td>
    <td>1018</td>
    <td>13222</td>
    <td>123222112</td>
    <td>2333</td>
    <td>4245308</td>
    <td>11290857</td>
    <td>12433</td>
</tr>
<tr>
    <td>12/15/12</td>
    <td>1023</td>
    <td>13312</td>
    <td>125036772</td>
    <td>2343</td>
    <td>4260258</td>
    <td>11341502</td>
    <td>12484</td>
</tr>
<tr>
    <td>12/16/12</td>
    <td>1029</td>
    <td>13373</td>
    <td>126922817</td>
    <td>2349</td>
    <td>4282434</td>
    <td>11416023</td>
    <td>12522</td>
</tr>
<tr>
    <td>12/17/12</td>
    <td>1037</td>
    <td>17002</td>
    <td>128901404</td>
    <td>2365</td>
    <td>4302936</td>
    <td>11486007</td>
    <td>12582</td>
</tr>
<tr>
    <td>12/18/12</td>
    <td>1042</td>
    <td>17144</td>
    <td>131355348</td>
    <td>2371</td>
    <td>4315685</td>
    <td>11530963</td>
    <td>12637</td>
</tr>
<tr>
    <td>12/19/12</td>
    <td>1045</td>
    <td>17199</td>
    <td>133791112</td>
    <td>2375</td>
    <td>4329691</td>
    <td>11579294</td>
    <td>12679</td>
</tr>
<tr>
    <td>12/20/12</td>
    <td>1048</td>
    <td>17313</td>
    <td>136277936</td>
    <td>2383</td>
    <td>4347036</td>
    <td>11635812</td>
    <td>12752</td>
</tr>
<tr>
    <td>12/21/12</td>
    <td>1050</td>
    <td>17387</td>
    <td>138841634</td>
    <td>2391</td>
    <td>4361604</td>
    <td>11686096</td>
    <td>12841</td>
</tr>
<tr>
    <td>12/22/12</td>
    <td>1056</td>
    <td>17543</td>
    <td>141490385</td>
    <td>2400</td>
    <td>4381924</td>
    <td>11753271</td>
    <td>13055</td>
</tr>
<tr>
    <td>12/23/12</td>
    <td>1062</td>
    <td>17660</td>
    <td>144273144</td>
    <td>2406</td>
    <td>4406635</td>
    <td>11834250</td>
    <td>13202</td>
</tr>
<tr>
    <td>12/24/12</td>
    <td>1066</td>
    <td>17851</td>
    <td>147031327</td>
    <td>2422</td>
    <td>4430280</td>
    <td>11911850</td>
    <td>13327</td>
</tr>
<tr>
    <td>12/25/12</td>
    <td>1071</td>
    <td>17930</td>
    <td>N/A</td>
    <td>N/A</td>
    <td>N/A</td>
    <td>N/A</td>
    <td>N/A</td>
</tr>
<tr>
    <td>12/26/12</td>
    <td>1079</td>
    <td>18102</td>
    <td>152874813</td>
    <td>2438</td>
    <td>4463727</td>
    <td>12021919</td>
    <td>13565</td>
</tr>
<tr>
    <td>12/27/12</td>
    <td>1084</td>
    <td>18238</td>
    <td>155915623</td>
    <td>2453</td>
    <td>4481685</td>
    <td>12081980</td>
    <td>13683</td>
</tr>
<tr>
    <td>12/28/12</td>
    <td>1086</td>
    <td>18401</td>
    <td>159065384</td>
    <td>2466</td>
    <td>4500657</td>
    <td>12145311</td>
    <td>13774</td>
</tr>
<tr>
    <td>12/29/12</td>
    <td>N/A</td>
    <td>18552</td>
    <td>162322887</td>
    <td>2478</td>
    <td>4521489</td>
    <td>12215474</td>
    <td>13857</td>
</tr>
<tr>
    <td>12/30/12</td>
    <td>1100</td>
    <td>18700</td>
    <td>165614082</td>
    <td>2490</td>
    <td>4542792</td>
    <td>12286601</td>
    <td>13978</td>
</tr>
<tr>
    <td>12/31/12</td>
    <td>1108</td>
    <td>18842</td>
    <td>169076660</td>
    <td>2499</td>
    <td>4564347</td>
    <td>12359829</td>
    <td>14059</td>
</tr>
</tbody>
</table>
</div>
</div>