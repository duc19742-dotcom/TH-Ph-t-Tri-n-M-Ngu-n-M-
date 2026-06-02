<!DOCTYPE html> 
<html lang="en"> 

<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 

    <title>Quản lý sản phẩm</title> 

    <link 
        href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" 
        rel="stylesheet"> 

        <link href="/assets/css/style.css" rel="stylesheet">

        
</head> 

<body> 

    <nav class="navbar navbar-expand-lg navbar-light bg-light"> 

        <a class="navbar-brand" href="#">Quản lý sản phẩm</a> 

        <button class="navbar-toggler" type="button" data-toggle="collapse" 
            data-target="#navbarNav" aria-controls="navbarNav" 
            aria-expanded="false" aria-label="Toggle navigation"> 

            <span class="navbar-toggler-icon"></span> 

        </button> 

        <div class="collapse navbar-collapse" id="navbarNav"> 

            <ul class="navbar-nav"> 

                <li class="nav-item"> 
                    <a class="nav-link" href="/Product">
                        Danh sách sản phẩm
                    </a> 
                </li> 

                <li class="nav-item"> 
                    <a class="nav-link" href="/Product/add">
                        Thêm sản phẩm
                    </a> 
                </li> 
                <li class="nav-item">
                    <a class="nav-link" href="/Product/cart">
                        Gio hang
                        (<?php echo array_sum(array_column($_SESSION['cart'] ?? [], 'quantity')); ?>)
                    </a>
                </li>
                 <?php if (SessionHelper::isLoggedIn()): ?> 
        <li class="nav-item">
            <span class="nav-link">
                <?php echo htmlspecialchars(SessionHelper::getUsername(), ENT_QUOTES, 'UTF-8'); ?>
                (<?php echo htmlspecialchars(SessionHelper::getRole(), ENT_QUOTES, 'UTF-8'); ?>)
            </span>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="/Account/logout">Dang xuat</a>
        </li>
    <?php else: ?>
        <li class="nav-item">
            <a class="nav-link" href="/Account/login">Dang nhap</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="/Account/register">Dang ky</a>
        </li>
    <?php endif; ?>

            </ul> 

        </div> 

    </nav> 

    <div class="container mt-4"> 
