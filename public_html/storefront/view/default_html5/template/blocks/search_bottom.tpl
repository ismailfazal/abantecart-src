<form id="search_form" class="form-search top-search">
    <input  type="hidden" name="filter_category_id" id="filter_category_id" value="0"/>
    <div class="btn-group search-bar">
    	<input type="text" id="filter_keyword" name="filter_keyword" autocomplete="off"
    		   class="pull-left input-medium search-query" placeholder="<?php echo $text_keyword; ?>" value=""
    		   class="btn dropup-toggle" data-toggle="dropup" href="#"
    			/>
    	 <div class="button-in-search" title="<?php echo $button_go; ?>"></div>
    <?php
    	$categories = $this->cache->get('category.list.0.0', (int)$this->config->get('storefront_language_id'), (int)$this->config->get('config_store_id'));
    	if($categories){
    		array_unshift($categories, array('category_id'=>0, 'name'=>'All Categories'));
    ?>
    	<ul class="dropup dropup-menu span2 noclose">
    		<!-- dropdown menu links -->
    		<li class="active"><a id="category_selected"><?php echo $categories[0]['name']?></a></li>
    		<li class="divider"></li>
    		<span id="search-category">
    		<?php foreach($categories as $category){ ?>
    			<li><a id="category_<?php echo $category['category_id']?>"><?php echo $category['name']?></a></li>
    		<?php } ?>
    		</span>
    	</ul>

    <?php } ?>
    </div>
</form>