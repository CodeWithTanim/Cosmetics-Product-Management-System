<?php 
 $products = $conn->query("SELECT p.*,b.name as bname,c.category FROM `products` p inner join brands b on p.brand_id = b.id inner join categories c on p.category_id = c.id where md5(p.id) = '{$_GET['id']}' ");
 if($products->num_rows > 0){
     foreach($products->fetch_assoc() as $k => $v){
         $$k= stripslashes($v);
     }
    $upload_path = base_app.'/uploads/product_'.$id;
    $img = "";
    if(is_dir($upload_path)){
        $fileO = scandir($upload_path);
        if(isset($fileO[2]))
            $img = "uploads/product_".$id."/".$fileO[2];
        // var_dump($fileO);
    }
    $inventory = $conn->query("SELECT * FROM inventory where product_id = ".$id." order by variant asc");
    $inv = array();
    while($ir = $inventory->fetch_assoc()){
        $ir['price'] = format_num($ir['price']);
        $ir['stock'] = $ir['quantity'];
        $sold = $conn->query("SELECT sum(quantity) FROM `order_list` where inventory_id = '{$ir['id']}' and order_id in (SELECT order_id from `sales`)")->fetch_array()[0];
        $sold = $sold > 0 ? $sold : 0;
        $ir['stock'] = $ir['stock'] - $sold;
        $inv[] = $ir;
    }
 }
 
?>
<style>
    .variant-item.active{
        border-color:var(--pink) !important;
    }
    .variant-item{
        cursor: pointer !important;
    }
</style>
<section class="py-5">
    <div class="container px-4 px-lg-5 my-5">
        
        <div class="row gx-4 gx-lg-5 align-items-center">
            <div class="col-md-6">
                <img class="card-img-top mb-5 mb-md-0 border border-dark" loading="lazy" id="display-img" src="<?php echo validate_image($img) ?>" alt="..." />
                <div class="mt-2 row gx-2 gx-lg-3 row-cols-4 row-cols-md-3 row-cols-xl-4 justify-content-start">
                    <?php 
                        foreach($fileO as $k => $img):
                            if(in_array($img,array('.','..')))
                                continue;
                    ?>
                    <div class="col">
                        <a href="javascript:void(0)" class="view-image <?php echo $k == 2 ? "active":'' ?>"><img src="<?php echo validate_image('uploads/product_'.$id.'/'.$img) ?>" loading="lazy"  class="img-thumbnail" alt=""></a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-6">
                <!-- <div class="small mb-1">SKU: BST-498</div> -->
                <h1 class="display-5 fw-bolder border-bottom border-primary pb-1"><?php echo $name ?></h1>
                <p class="m-0"><small>Brand: <?php echo $bname ?></small></p>
                <div class="fs-5 mb-5">
                &#8369; <span id="price"><?php echo isset($inv[0]['price']) ?  format_num($inv[0]['price']) : "--" ?></span>
                <br>
                <span><small><span class="text-muted">Available Stock:</span> <span id="avail"><?php echo isset($inv[0]['stock']) ? format_num($inv[0]['stock']) : "--" ?></span></small></span>
                <h5>Variant</h5>
                <?php 
                    $active = false;
                foreach($inv as $k => $v):
                ?>
                <span class="variant-item border rounded-pill bg-gradient-light mr-2 text-xs px-3 <?= (!$active) ? "active" : "" ?>" data-key = "<?= $k ?>"><?= $v['variant'] ?></span>
                <?php 
                    $active = true;
                     endforeach;
                ?>
                </div>
                <form action="" id="add-cart">
                <div class="d-flex">
                    <input type="hidden" name="price" value="<?php echo isset($inv[0]['price']) ? $inv[0]['price'] : 0 ?>">
                    <input type="hidden" name="inventory_id" value="<?php echo isset($inv[0]['id']) ? $inv[0]['id'] : '' ?>">
                    <input class="form-control text-center me-3" id="inputQuantity" type="num" value="1" style="max-width: 3rem" name="quantity" />
                    <button class="btn btn-outline-dark flex-shrink-0" type="submit">
                        <i class="bi-cart-fill me-1"></i>
                        Add to cart
                    </button>
                </div>
                </form>
                <p class="lead"><?php echo stripslashes(html_entity_decode($specs)) ?></p>
                
            </div>
        </div>
    </div>
</section>
<!-- Related items section-->
<section class="py-5 bg-light">
    <div class="container px-4 px-lg-5 mt-5">
        <h2 class="fw-bolder mb-4">Related products</h2>
        <div class="row gx-4 gx-lg-5 row-cols-1 row-cols-md-3 row-cols-xl-4 justify-content-center">
        <?php 
            $products = $conn->query("SELECT p.*,b.name as bname,c.category  FROM `products` p inner join brands b on p.brand_id = b.id inner join categories c on p.category_id = c.id where p.status = 1 and (p.category_id = '{$category_id}' or p.brand_id = '{$brand_id}') and p.id !='{$id}' order by rand() limit 4 ");
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
                $rinventory = $conn->query("SELECT distinct(`price`) FROM inventory where product_id = ".$row['id']." order by `price` asc");
                $rinv = array();
                while($ir = $rinventory->fetch_assoc()){
                    $rinv[] = format_num($ir['price']);
                }
                $price = '';
                if(isset($rinv[0]))
                $price .= $rinv[0];
                if(count($rinv) > 1){
                $price .= " ~ ".$rinv[count($rinv) - 1];

                }
        ?>
            <div class="col mb-5">
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
                            <span><b class="text-muted">Price: </b><?php echo $price ?></span>
                            <p class="m-0"><small>Brand: <?php echo $row['bname'] ?></small></p>
                            <p class="m-0"><small><span class="text-muted">Category:</span> <?php echo $row['category'] ?></small></p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<script>
    var inv = $.parseJSON('<?php echo json_encode($inv) ?>');
    $(function(){
        $('.view-image').click(function(){
            var _img = $(this).find('img').attr('src');
            $('#display-img').attr('src',_img);
            $('.view-image').removeClass("active")
            $(this).addClass("active")
        })
        $('.variant-item').click(function(){
            var k = $(this).attr('data-key');
            $('.variant-item').removeClass("active")
            $(this).addClass("active")
            if(!!inv[k]){
                $('#price').text(inv[k].price)
                $('[name="price"]').val(inv[k].price)
                $('#avail').text(inv[k].stock)
                $('[name="inventory_id"]').val(inv[k].id)
            }else{
                alert_toast("An error occured",'error')
            }

        })

        $('#add-cart').submit(function(e){
            e.preventDefault();
            if('<?= $_settings->userdata('id') > 0 || $_settings->userdata('login_type') == 2 ?>' != '1'){
                uni_modal("","login.php");
                return false;
            }
            start_loader();
            $.ajax({
                url:'classes/Master.php?f=add_to_cart',
                data:$(this).serialize(),
                method:'POST',
                dataType:"json",
                error:err=>{
                    console.log(err)
                    alert_toast("an error occured",'error')
                    end_loader()
                },
                success:function(resp){
                    if(typeof resp == 'object' && resp.status=='success'){
                        alert_toast("Product added to cart.",'success')
                        $('#cart-count').text(resp.cart_count)
                    }else{
                        console.log(resp)
                        alert_toast("an error occured",'error')
                    }
                    end_loader();
                }
            })
        })
    })
</script>