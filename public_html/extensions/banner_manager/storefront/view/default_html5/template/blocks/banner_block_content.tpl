<section id="banner_<?php echo $block_details['block_txt_id'] . '_' . $block_details['instance_id'] ?>" class="container mt20">
    <div class="container">
<?php if ( $block_framed ) { ?>
		<div class="block_frame block_frame_<?php echo $block_details['block_txt_id']; ?>"
					 id="block_frame_<?php echo $block_details['block_txt_id'] . '_' . $block_details['instance_id'] ?>">
      <h1 class="heading1"><span class="maintext"><?php echo $heading_title; ?></span><span class="subtext"><?php echo $heading_subtitle; ?></span></h1>
<?php } ?>
<?php if($content){
		foreach($content as $banner){
			echo '<div class="pull-left mr10" >';
			if($banner['banner_type']==1){
				foreach($banner['images'] as $img){
					echo '<a id="'.$banner['banner_id'].'" href="'.$banner['target_url'].'" '.($banner['blank'] ? ' target="_blank" ': '').'>';
					if($img['origin']=='internal'){
						echo '<img src="'.$img['main_url'].'" title="'.$img['title'].'" alt="'.$img['title'].'">';
					}else{
						echo $img['main_html'];
					}
					echo '</a>';
				}
			}else{
				echo $banner['description'];
			}
		echo '</div>';
		}
}?>
<?php
if ( $block_framed ) { ?>
		</div>
<?php } ?>
	</div>
</section>

<script language="javascript">
	$('.banner a').live('click',
		function(){
			var that = this;
			$.ajax({
                    url: '<?php echo $stat_url; ?>'+'&type=2&banner_id=' + $(that).prop('id'),
                    type: 'GET',
                    dataType: 'json'
                });
		}
	);
</script>