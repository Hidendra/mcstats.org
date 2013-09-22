<?php
$current = 'january-2013';

// Analytics
$visits = 17398;
$unique_visitors = 9551;
$new_visitors = 904;
$pageviews = 50740;
$pages_visit = 2.92;
$avg_visit = '00:03:42';

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
    <td>01/01/13</td>
    <td>1111</td>
    <td>18981</td>
    <td>172224023</td>
    <td>2513</td>
    <td>4587782</td>
    <td>12439498</td>
    <td>14144</td>
</tr>
<tr>
    <td>01/02/13</td>
    <td>1123</td>
    <td>19115</td>
    <td>175749115</td>
    <td>2521</td>
    <td>4606693</td>
    <td>12504006</td>
    <td>14243</td>
</tr>
<tr>
    <td>01/03/13</td>
    <td>1138</td>
    <td>19211</td>
    <td>179392971</td>
    <td>2530</td>
    <td>4627179</td>
    <td>12574751</td>
    <td>14321</td>
</tr>
<tr>
    <td>01/04/13</td>
    <td>1155</td>
    <td>19385</td>
    <td>183093715</td>
    <td>2547</td>
    <td>4646988</td>
    <td>12643178</td>
    <td>14409</td>
</tr>
<tr>
    <td>01/05/13</td>
    <td>1160</td>
    <td>19528</td>
    <td>186869286</td>
    <td>2561</td>
    <td>4669103</td>
    <td>12720513</td>
    <td>14486</td>
</tr>
<tr>
    <td>01/06/13</td>
    <td>1169</td>
    <td>19634</td>
    <td>190707954</td>
    <td>2567</td>
    <td>4692355</td>
    <td>12803475</td>
    <td>14558</td>
</tr>
<tr>
    <td>01/07/13</td>
    <td>1178</td>
    <td>19721</td>
    <td>194614083</td>
    <td>2575</td>
    <td>4711880</td>
    <td>12872209</td>
    <td>14621</td>
</tr>
<tr>
    <td>01/08/13</td>
    <td>1182</td>
    <td>19821</td>
    <td>198518070</td>
    <td>2586</td>
    <td>4729279</td>
    <td>12932361</td>
    <td>14706</td>
</tr>
<tr>
    <td>01/09/13</td>
    <td>1193</td>
    <td>19966</td>
    <td>202479903</td>
    <td>2599</td>
    <td>4746723</td>
    <td>12991781</td>
    <td>14807</td>
</tr>
<tr>
    <td>01/10/13</td>
    <td>1193</td>
    <td>20015</td>
    <td>n/a</td>
    <td>n/a</td>
    <td>n/a</td>
    <td>n/a</td>
    <td>n/a</td>
</tr>
<tr>
    <td>01/11/13</td>
    <td>1200</td>
    <td>20165</td>
    <td>206133442</td>
    <td>2620</td>
    <td>4777177</td>
    <td>13096187</td>
    <td>14977</td>
</tr>
<tr>
    <td>01/12/13</td>
    <td>1205</td>
    <td>20267</td>
    <td>208692606</td>
    <td>2637</td>
    <td>4796715</td>
    <td>13164623</td>
    <td>15058</td>
</tr>
<tr>
    <td>01/13/13</td>
    <td>1214</td>
    <td>20436</td>
    <td>209378878</td>
    <td>2647</td>
    <td>4815691</td>
    <td>13228443</td>
    <td>15169</td>
</tr>
<tr>
    <td>01/14/13</td>
    <td>1222</td>
    <td>20503</td>
    <td>210053562</td>
    <td>2655</td>
    <td>4829612</td>
    <td>13277024</td>
    <td>15247</td>
</tr>
<tr>
    <td>01/15/13</td>
    <td>1230</td>
    <td>20635</td>
    <td>212746059</td>
    <td>2664</td>
    <td>4841832</td>
    <td>13319333</td>
    <td>15288</td>
</tr>
<tr>
    <td>01/16/13</td>
    <td>1242</td>
    <td>20775</td>
    <td>213907603</td>
    <td>2680</td>
    <td>4851952</td>
    <td>13357960</td>
    <td>15389</td>
</tr>
<tr>
    <td>01/17/13</td>
    <td>1251</td>
    <td>20941</td>
    <td>217988331</td>
    <td>2693</td>
    <td>4874012</td>
    <td>13429669</td>
    <td>15488</td>
</tr>
<tr>
    <td>01/18/13</td>
    <td>1264</td>
    <td>21156</td>
    <td>222073047</td>
    <td>2715</td>
    <td>4891900</td>
    <td>13488631</td>
    <td>15608</td>
</tr>
<tr>
    <td>01/19/13</td>
    <td>1273</td>
    <td>21338</td>
    <td>226464691</td>
    <td>2730</td>
    <td>4913904</td>
    <td>13560823</td>
    <td>15696</td>
</tr>
<tr>
    <td>01/20/13</td>
    <td>1290</td>
    <td>21531</td>
    <td>231050531</td>
    <td>2748</td>
    <td>4940148</td>
    <td>13648491</td>
    <td>15809</td>
</tr>
<tr>
    <td>01/21/13</td>
    <td>1302</td>
    <td>21714</td>
    <td>235344742</td>
    <td>2765</td>
    <td>4961604</td>
    <td>13719981</td>
    <td>15959</td>
</tr>
<tr>
    <td>01/22/13</td>
    <td>1313</td>
    <td>21848</td>
    <td>239929680</td>
    <td>2777</td>
    <td>4981710</td>
    <td>13787150</td>
    <td>16053</td>
</tr>
<tr>
    <td>01/23/13</td>
    <td>1334</td>
    <td>21938</td>
    <td>244385541</td>
    <td>2785</td>
    <td>4998727</td>
    <td>13844164</td>
    <td>16134</td>
</tr>
<tr>
    <td>01/24/13</td>
    <td>1346</td>
    <td>22175</td>
    <td>248137954</td>
    <td>2808</td>
    <td>5017339</td>
    <td>13906274</td>
    <td>16234</td>
</tr>
<tr>
    <td>01/25/13</td>
    <td>1355</td>
    <td>22286</td>
    <td>252648675</td>
    <td>2817</td>
    <td>5035773</td>
    <td>13968605</td>
    <td>16312</td>
</tr>
<tr>
    <td>01/26/13</td>
    <td>1365</td>
    <td>22442</td>
    <td>257226118</td>
    <td>2833</td>
    <td>5060523</td>
    <td>14052189</td>
    <td>16410</td>
</tr>
<tr>
    <td>01/28/13</td>
    <td>1386</td>
    <td>22785</td>
    <td>263161538</td>
    <td>2865</td>
    <td>5102161</td>
    <td>14195615</td>
    <td>16608</td>
</tr>
<tr>
    <td>01/29/13</td>
    <td>1393</td>
    <td>22862</td>
    <td>266537419</td>
    <td>2873</td>
    <td>5120028</td>
    <td>14254852</td>
    <td>16681</td>
</tr>
<tr>
    <td>01/30/13</td>
    <td>1501</td>
    <td>23040</td>
    <td>271563574</td>
    <td>2889</td>
    <td>5136472</td>
    <td>14308899</td>
    <td>16789</td>
</tr>
<tr>
    <td>01/31/13</td>
    <td>1507</td>
    <td>23214</td>
    <td>276562298</td>
    <td>2904</td>
    <td>5154084</td>
    <td>14368290</td>
    <td>16905</td>
</tr>
</tbody>
</table>
</div>
</div>