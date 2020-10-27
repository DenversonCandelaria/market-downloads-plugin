<div class="container">
    <div class="row">

        <div class="col-lg-12">
            <div class="market-reports">
                <div class="container" style="padding-left: 0px; padding-right: 0px;">
                    <div class="header-container">
                        <div class="row">
                            <div class="col-lg-5 col-md-12">
                                <h1 class="header-36">
                                    <?php echo get_the_title(); ?>
                                </h1>
                                <div class="line"></div>

                            </div>

                            <div class="col-lg-7 col-md-12">
                                <div class="tab-panel inside-drop-area fade show active" id="events-tab-content" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="inside-drop-content">
                                                <?php if($categories) { ?>
                                                    <select class="inside-select-category" id="category-select" name="Year" required="">
                                                        <option value="" selected="selected" style="">Select Category</option>
                                                        <?php foreach ($categories as $key => $category) { ?>
                                                            <option value="<?php echo $category->term_id;  ?>"><?php echo $category->name; ?></option>
                                                            <?php
                                                        } ?>
                                                    </select>
                                                    <?php
                                                } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div><?php 
                if($categories){
                    foreach ($categories as $key => $category) {
                        $cat_id = $category->term_id;
                        $args = array(
                            'post_type'=>'market-data',
                            'posts_per_page'=>-1,
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'market_data_category',
                                    'field' => 'term_id',
                                    'terms' => $cat_id,
                                )
                            )
                        );
                        $md_pages = get_posts($args); ?>
                        <div class="market-reports-group" id="<?php echo $cat_id; ?>">
                            <div class="market-reports-group-title"><?php echo $category->name; ?></div><?php 
                            if($md_pages){ ?>
                                <div class="row"><?php 
                                    foreach ($md_pages as $key => $md_page) { ?>
                                        <div class="col-lg-6" >
                                            <div class="market-reports-item">
                                                <div class="market-reports-title"><?php echo $md_page->post_title; ?></div>
                                                <a href="<?php echo get_permalink($md_page->ID); ?>">
                                                    <button class="mr-view-data-btn">
                                                        <img class="page-icon" src="<?php echo get_stylesheet_directory_uri().'/assets/img/next-page.png'; ?>">
                                                    </button>
                                                </a>
                                                <div class="clear"></div>
                                            </div>
                                        </div><?php
                                    } ?>
                                </div><?php
                            }else{ ?>
                                <div class="empty-notice">No Market Data Page Available</div><?php 
                            } ?>
                        </div><?php
                    }
                }else{ ?>
                    <div class="empty-notice">No Market Data Available</div><?php
                } ?>
                
            </div>
        </div>
    </div>
</div>