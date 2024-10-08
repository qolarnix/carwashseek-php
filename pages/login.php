<?php

declare(strict_types=1);

echo $view->render('header');
?>

<section class="py-0 px-6">
    <div class="mx-auto max-w-7xl">

        <div class="flex flex-col gap-2 items-center justify-center h-screen">
            <div id="res"></div>
            
            <div class="rounded shadow bg-white p-6">
                <h1 class="mb-2 text-xl font-bold">Login</h1>
                <form hx-post="actions/login.php"
                    hx-trigger="submit"
                    hx-target="#res" 
                    class="flex flex-col gap-2 items-start">
                    <input required 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="example@gmail.com" 
                        class="rounded">
                    <input 
                        type="submit" 
                        value="Get link" 
                        class="bg-blue-600 text-white p-2 rounded">
                </form>
            </div>
        </div>

    </div>
</section>

<?php
echo $view->render('footer');