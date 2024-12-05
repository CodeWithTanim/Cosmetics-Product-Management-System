<?php 
$title = "";
$sub_title = "";
if(isset($_GET['c']) && isset($_GET['s'])){
    $cat_qry = $conn->query("SELECT * FROM categories where md5(id) = '{$_GET['c']}'");
    if($cat_qry->num_rows > 0){
        $result =$cat_qry->fetch_assoc();
        $title = $result['category'];
        $cat_description = $result['description'];
    }
 $sub_cat_qry = $conn->query("SELECT * FROM sub_categories where md5(id) = '{$_GET['s']}'");
    if($sub_cat_qry->num_rows > 0){
        $result =$sub_cat_qry->fetch_assoc();
        $sub_title = $result['sub_category'];
        $sub_cat_description = $result['description'];
    }
}
elseif(isset($_GET['c'])){
    $cat_qry = $conn->query("SELECT * FROM categories where md5(id) = '{$_GET['c']}'");
    if($cat_qry->num_rows > 0){
        $result =$cat_qry->fetch_assoc();
        $title = $result['category'];
        $cat_description = $result['description'];
    }
}
elseif(isset($_GET['s'])){
    $sub_cat_qry = $conn->query("SELECT * FROM sub_categories where md5(id) = '{$_GET['s']}'");
    if($sub_cat_qry->num_rows > 0){
        $result =$sub_cat_qry->fetch_assoc();
        $sub_title = $result['sub_category'];
        $sub_cat_description = $result['description'];
    }
}
$brands = isset($_GET['b']) ? json_decode(urldecode($_GET['b'])) : array();
?>
<!-- Header-->
<header class="bg-dark py-5" id="main-header">
    <div class="container px-4 px-lg-5 my-5">
        <div class="text-center text-white">
            <h1 class="display-4 fw-bolder"><?php echo $title ?></h1>
            <p class="lead fw-normal text-white-50 mb-0"><?php echo $sub_title ?></p>
        </div>
    </div>
</header>
<!-- Section-->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-3 border-right mb-2 pb-3">
                <h4><b>Brands</b></h4>
                <ul class="list-group">
                    <a href="" class="list-group-item list-group-item-action">
                        <div class="icheck-primary d-inline">
                            <input type="checkbox" id="brandAll" >
                            <label for="brandAll">
                                All
                            </label>
                        </div>
                    </a>
                    <?php 
                    $qry = $conn->query("SELECT * FROM brands where status =1 order by name asc");
                    while($row=$qry->fetch_assoc()):
                    ?>
                    <li class="list-group-item list-group-item-action">
                        <div class="icheck-primary d-inline">
                            <input type="checkbox" id="brand-item-<?php echo $row['id'] ?>" <?php echo in_array($row['id'],$brands) ? "checked" : "" ?> class="brand-item" value="<?php echo $row['id'] ?>">
                            <label for="brand-item-<?php echo $row['id'] ?>">
                                    <?php echo $row['name'] ?>
                            </label>
                        </div>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <div class="col-md-9">
                <div class="container-fluid p-0">
                    <?php if(isset($_GET['search'])): ?>
                        <h4 class="text-center py-5"><b>Search Results for '<?php echo $_GET['search'] ?>'</b></h4>
                    <?php endif; ?>
                <div class="row gx-2 gx-lg-2 row-cols-1 row-cols-md-3 row-cols-xl-4">
                        
                        <?php 
                            $whereData = "";
                            if(isset($_GET['search']))
                                $whereData = " and (p.name LIKE '%{$_GET['search']}%' or b.name LIKE '%{$_GET['search']}%' or p.specs LIKE '%{$_GET['search']}%')";
                            elseif(isset($_GET['c']) && isset($_GET['s']))
                                $whereData = " and (md5(category_id) = '{$_GET['c']}' and md5(sub_category_id) = '{$_GET['s']}')";
                            elseif(isset($_GET['c']) && !isset($_GET['s']))
                                $whereData = " and md5(category_id) = '{$_GET['c']}' ";
                            elseif(isset($_GET['s']) && !isset($_GET['c']))
                                $whereData = " and md5(sub_category_id) = '{$_GET['s']}' ";
                            $bwhere = "";
                            if(count($brands)>0)
                                $bwhere = " and p.brand_id in (".implode(",",$brands).") " ;
                            $products = $conn->query("SELECT p.*,b.name as bname, c.category FROM `products` p inner join brands b on p.brand_id = b.id inner join categories c on p.category_id = c.id where p.status = 1 {$whereData} {$bwhere} order by rand() ");
                            while($row = $products->fetch_assoc()):
                                $upload_path = base_app.'/uploads/product_'.$row['id'];
                                $img = "";
                                if(is_dir($upload_path)){
                                    $fileO = scandir($upload_path);
                                    if(isset($fileO[2]))
                                        $img = "uploads/product_".$row['id']."/".$fileO[2];
                                    // var_dump($fileO);
                                }
                                foreach($row as $k=> $v){
                                    $row[$k] = trim(stripslashes($v));
                                }
                                $inventory = $conn->query("SELECT distinct(`price`) FROM inventory where product_id = ".$row['id']." order by `price` asc");
                                $inv = array();
                                while($ir = $inventory->fetch_assoc()){
                                    $inv[] = format_num($ir['price']);
                                }
                                $price = '';
                                if(isset($inv[0]))
                                $price .= $inv[0];
                                if(count($inv) > 1){
                                $price .= " ~ ".$inv[count($inv) - 1];

                                }
                        ?>
                        <div class="col-md-12 mb-5">
                            <a class="card product-item text-reset text-decoration-none" href=".?p=view_product&id=<?php echo md5($row['id']) ?>">
                                <!-- Product image-->
                                <div class="overflow-hidden shadow product-holder">
                                    <img class="card-img-top w-100 product-cover" src="<?php echo validate_image($img) ?>" alt="..." />
                                </div>
                                <!-- Product details-->
                                <div class="card-body p-4">
                                    <div class="">
                                        <!-- Product name-->
                                        <h5 class="fw-bolder"><?php echo $row['name'] ?></h5>
                                        <!-- Product price-->
                                        <span><b>Price: </b><?php echo $price ?></span>
                                    </div>
                                    <p class="m-0"><small>Brand: <?php echo $row['bname'] ?></small></p>
                                    <p class="m-0"><small>Category: <?php echo $row['category'] ?></small></p>
                                </div>
                            </a>
                        </div>
                        <?php endwhile; ?>
                        <?php 
                            if($products->num_rows <= 0){
                                echo "<h4 class='text-center'><b>No Product Listed.</b></h4>";
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    function _filter(){
        var brands = []
            $('.brand-item:checked').each(function(){
                brands.push($(this).val())
            })
        _b = JSON.stringify(brands)
        var checked = $('.brand-item:checked').length
        var total = $('.brand-item').length
        if(checked == total)
            location.href="./?p=products<?= isset($_GET['c']) ? "&c=".$_GET['c'] : "" ?>";
        else
            location.href="./?p=products<?= isset($_GET['c']) ? "&c=".$_GET['c'] : "" ?>&b="+encodeURI(_b);
    }
    function check_filter(){
        var checked = $('.brand-item:checked').length
        var total = $('.brand-item').length
        if(checked == total){
            $('#brandAll').attr('checked',true)
        }else{
            $('#brandAll').attr('checked',false)
        }
        if('<?php echo isset($_GET['b']) ?>' == '')
            $('#brandAll,.brand-item').attr('checked',true)
    }
    $(function(){
        check_filter()
        $('#brandAll').change(function(){
            if($(this).is(':checked') == true){
                $('.brand-item').attr('checked',true)
            }else{
                $('.brand-item').attr('checked',false)
            }
            _filter()
        })
        $('.brand-item').change(function(){
            _filter()
        })
    })
</script>