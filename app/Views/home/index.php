<div class="row">
    <div class="col-md-8">
        <h1>Welcome to ZenithPHP Framework</h1>
        <p class="lead">A modern, lightweight PHP MVC framework with built-in authentication and comprehensive features.</p>
        
        <?php if ($user): ?>
            <div class="alert alert-success">
                <h4>Hello, <?= $user->name ?>!</h4>
                <p>You are successfully logged in.</p>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <h4>Get Started</h4>
                <p>Please <a href="/login">login</a> or <a href="/register">register</a> to access protected features.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Framework Features</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li>✅ MVC Architecture</li>
                    <li>✅ Database Migrations</li>
                    <li>✅ Authentication System</li>
                    <li>✅ Google OAuth</li>
                    <li>✅ Middleware System</li>
                    <li>✅ Template Engine</li>
                    <li>✅ Routing System</li>
                    <li>✅ Composer Compatible</li>
                </ul>
            </div>
        </div>
    </div>
</div>