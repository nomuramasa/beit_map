<?php
$array = array(
    "foo" => "bar",
    42    => 24,
    "multi" => array(
         "dimensional" => array(
             "array" => "foo"
         )
    )
);

echo '<pre>';
var_dump($array);
echo '</pre>';


?>

<script>
	var array = <?php echo json_encode($array); ?>;
	console.log(array); 
</script>