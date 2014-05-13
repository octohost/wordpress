<h3> Overview </h3> 
<p>
This is a helpful WordPress options page, built using the Settings API, available since version 2.7. 
</p>
<p>
To use it, simply copy this script into either your functions.php file, or a sub-file. If you choose the latter, just make sure that you include it from functions.php. 
</p>
<img src="http://content.screencast.com/users/JeffreyWay/folders/Jing/media/d4bb9b2a-528e-4c77-bad2-134735b4bbc9/00000004.png" alt="Snapshot" />

<h3> Usage </h3>
 <p>
Once you have set your options, you can access them by doing: </p>
<pre>
	<code>
	<?php 
	$options = get_options($plugin_options);
	echo $options[key]
	?> 	
	</code>
</pre>
<p>Just replace "key" with the name of the options that you want. To view all the keys that are available, call print_r on $options: print_r($options). </p>

