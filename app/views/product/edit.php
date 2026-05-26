<?php include 'app/views/shares/header.php'; ?> 
 
<h1>Sửa sản phẩm</h1> 

<?php if (!empty($errors)): ?> 
    <div class="alert alert-danger"> 
        <ul> 
            <?php foreach ($errors as $error): ?> 
                <li>
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </li> 
            <?php endforeach; ?> 
        </ul> 
    </div> 
<?php endif; ?>

<form method="POST" 
      action="/Product/update" 
      enctype="multipart/form-data" 
      > 

    <input type="hidden" 
           name="id" 
           value="<?php echo $product->id; ?>"> 

    <div class="form-group"> 

        <label for="name">Tên sản phẩm:</label> 

        <input type="text" 
               id="name" 
               name="name" 
               class="form-control" 
               value="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>" 
               required> 

    </div> 

    <div class="form-group"> 

        <label for="description">Mô tả:</label> 

        <textarea id="description" 
                  name="description" 
                  class="form-control" 
                  required><?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?></textarea> 

    </div> 

    <div class="form-group"> 

        <label for="price">Giá:</label> 

        <input type="number" 
               id="price" 
               name="price" 
               class="form-control" 
               step="0.01" 
               value="<?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8'); ?>" 
               required> 

    </div> 

    <div class="form-group"> 

        <label for="category_id">Danh mục:</label> 

        <select id="category_id" 
                name="category_id" 
                class="form-control" 
                required> 

            <?php foreach ($categories as $category): ?> 

                <option value="<?php echo $category->id; ?>" 
                    <?php echo $category->id == $product->category_id ? 'selected' : ''; ?>> 

                    <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?> 

                </option> 

            <?php endforeach; ?> 

        </select> 

    </div> 

    <div class="form-group"> 

        <label for="image">Hình ảnh:</label> 

        <input type="file" 
               id="image" 
               name="image" 
               class="form-control-file"
               accept="image/jpeg,image/png,image/gif,image/webp"> 

        <?php if ($product->image): ?> 

            <img src="/<?php echo htmlspecialchars($product->image, ENT_QUOTES, 'UTF-8'); ?>" 
                 alt="Product Image" 
                 style="max-width: 100px;"> 

        <?php endif; ?> 

    </div> 

    <button type="submit" class="btn btn-primary">
        Lưu thay đổi
    </button> 

</form> 

<a href="/Product" 
   class="btn btn-secondary mt-2">
    Quay lại danh sách sản phẩm
</a>

<?php include 'app/views/shares/footer.php'; ?>
