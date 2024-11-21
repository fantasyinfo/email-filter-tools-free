<?php

$home = 'http://localhost/email-filter-tools-free';
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<style>
    /* body {
        background-color: #f4f6f9;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        margin: 0;
        font-family: 'Arial', sans-serif;
    } */

    .tools-container {
        background-color: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 40px;
        max-width: 1200px;
        width: 100%;
    }

    .tool-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        margin-bottom: 20px;
    }

    .tool-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        border-color: #007bff;
    }

    .tool-card .icon {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.8;
        transition: opacity 0.3s ease;
    }

    .tool-card:hover .icon {
        opacity: 1;
    }

    .tool-card .btn {
        width: 100%;
        border-radius: 10px;
    }

    .tool-card h5 {
        margin-bottom: 10px;
        font-weight: 600;
        color: #333;
    }

    .tool-card p {
        color: #6c757d;
        font-size: 0.9rem;
    }
</style>

<div class="container my-5">
    <div class="tools-container">
        <h2 class="text-center mb-5 text-primary">Free Email Management Tools</h2>
        <div class="row">
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="tool-card">
                    <div class="icon text-primary">
                        <i class="fas fa-filter"></i>
                    </div>
                    <h5>Email Filter</h5>
                    <p>Refine and organize your email lists efficiently</p>
                    <a href="<?= $home ?>/emails-filter" class="btn btn-outline-primary">
                        Open Tool <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="tool-card">
                    <div class="icon text-success">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <h5>Email Divider</h5>
                    <p>Divide emails from various syntax quickly</p>
                    <a href="<?= $home ?>/emails-divider" class="btn btn-outline-success">
                        Open Tool <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="tool-card">
                    <div class="icon text-info">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h5>Domains Extractor</h5>
                    <p>Extract unique domain names with ease</p>
                    <a href="<?= $home ?>/domains-extractor/index.php" class="btn btn-outline-info">
                        Open Tool <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="tool-card">
                    <div class="icon text-warning">
                        <i class="fas fa-code-merge"></i>
                    </div>
                    <h5>Email Merger</h5>
                    <p>Combine multiple email lists seamlessly</p>
                    <a href="<?= $home ?>/emails-merger/index.php" class="btn btn-outline-warning">
                        Open Tool <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="tool-card">
                    <div class="icon text-danger">
                        <i class="fa-regular fa-copy"></i>
                    </div>
                    <h5>Email Splitter</h5>
                    <p>Divide email lists into precise segments</p>
                    <a href="<?= $home ?>/emails-splitter/index.php" class="btn btn-outline-danger">
                        Open Tool <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>