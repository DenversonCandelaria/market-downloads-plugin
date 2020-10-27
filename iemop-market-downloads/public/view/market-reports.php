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
                                                <select class="inside-select-category"  id="category-select" name="Year" required="">
                                                    <option value="" selected="selected" style="">Select Category</option>
                                                    <?php foreach ($categories as $key => $category) { ?>
                                                        <option value="<?php echo $category->term_id;  ?>"><?php echo $category->name; ?></option>
                                                        <?php
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php 
                if($categories){ // Parent Categories
                    // echo json_encode($categories);
                    foreach ($categories as $key => $category) { ?>
                        <div id="<?php echo $category->term_id; ?>" class="market-reports-group">
                            <div class="page-text">
                                <div class="page-title"><?php echo $category->name; ?></div>               
                            </div><?php
                            $childs = $this->get_child_categories($category->term_id); // Child Categories
                            if($childs){
                                foreach ($childs as $key => $child) { ?>
                                    <div>
                                        <div class="market-reports-group-title"><?php echo $child->name; ?></div><?php
                                        $mr_pages = $this->get_mr_pages($child->term_id); // Market Report Pages
                                        if($mr_pages){ ?>
                                            <div class="row"><?php
                                                foreach ($mr_pages as $key => $mr_page) { ?>
                                                    <div class="col-lg-6" >
                                                        <div class="market-reports-item">
                                                            <div class="market-reports-title"><?php echo $mr_page->post_title; ?></div>
                                                            <a href="<?php echo get_permalink($mr_page->ID); ?>">
                                                                <button class="mr-view-data-btn">
                                                                    <img class="page-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/next-page.png">
                                                                </button>
                                                            </a>
                                                            <div class="clear"></div>
                                                        </div>
                                                    </div><?php
                                                } ?>
                                            </div><?php
                                        }else{ ?>
                                            <div class="empty-notice">No Market Reports Page Available</div><?php 
                                        } ?>
                                       
                                    </div>
                                    <?php
                                }
                            }else{ ?>
                                <div class="empty-notice">No Market Reports Page Available</div><?php 
                            } ?>
                        </div><?php
                    }
                }else{ ?>
                    <div class="empty-notice">No Market Reports Page Available</div><?php 
                } ?>
            </div>
        </div>
    </div>
</div>