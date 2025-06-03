<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ZenithPHP Framework' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">ZenithPHP</a>
            <div class="navbar-nav ms-auto">
                <?php if (\Core\Auth::check()): ?>
                    <span class="navbar-text me-3">
                        Hello, <?= \Core\Auth::user()->name ?>!
                    </span>
                    <a class="nav-link" href="/logout">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="/login">Login</a>
                    <a class="nav-link" href="/register">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (\Core\Session::flash('error')): ?>
            <div class="alert alert-danger">
                <?= \Core\Session::flash('error') ?>
            </div>
        <?php endif; ?>

        <?php if (\Core\Session::flash('success')): ?>
            <div class="alert alert-success">
                <?= \Core\Session::flash('success') ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>