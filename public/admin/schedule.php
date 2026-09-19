<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule - Admin - DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="../assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">Admin</h1>
        <?php require __DIR__ . '/../../src/components/admin-navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold">Schedule</h2>
                <div class="flex items-center gap-2">
                    <a href="#" class="text-gray-400 hover:text-white px-1">&lsaquo;</a>
                    <div class="flex gap-1 bg-gray-900 rounded-lg p-1">
                        <a href="#" class="px-3 py-1.5 rounded-md text-sm text-gray-400 hover:text-white">Mon 14</a>
                        <a href="#" class="px-3 py-1.5 rounded-md text-sm text-gray-400 hover:text-white">Tue 15</a>
                        <a href="#" class="px-3 py-1.5 rounded-md text-sm text-gray-400 hover:text-white">Wed 16</a>
                        <a href="#" class="px-3 py-1.5 rounded-md text-sm text-gray-400 hover:text-white">Thu 17</a>
                        <a href="#" class="px-3 py-1.5 rounded-md text-sm text-gray-400 hover:text-white">Fri 18</a>
                        <a href="#" class="px-3 py-1.5 rounded-md text-sm bg-blue-900 text-white">Sat 19</a>
                        <a href="#" class="px-3 py-1.5 rounded-md text-sm text-gray-400 hover:text-white">Sun 20</a>
                    </div>
                    <a href="#" class="text-gray-400 hover:text-white px-1">&rsaquo;</a>
                    <input type="date" class="bg-gray-900 border border-gray-800 rounded-lg px-2 py-1.5 text-sm text-gray-400 ml-1">
                </div>
            </div>

            <div class="overflow-x-auto">
                <div class="min-w-190">
                    <div class="grid grid-cols-[110px_repeat(12,1fr)] text-xs text-gray-500 mb-2">
                        <div></div>
                        <div>12 PM</div>
                        <div>1 PM</div>
                        <div>2 PM</div>
                        <div>3 PM</div>
                        <div>4 PM</div>
                        <div>5 PM</div>
                        <div>6 PM</div>
                        <div>7 PM</div>
                        <div>8 PM</div>
                        <div>9 PM</div>
                        <div>10 PM</div>
                        <div>11 PM</div>
                    </div>

                    <div class="space-y-2">
                        <div class="grid grid-cols-[110px_repeat(12,1fr)] items-center">
                            <div class="text-sm text-gray-400 pr-2">Standard Digital</div>
                            <div class="col-span-12 h-14 grid grid-cols-12 rounded-lg overflow-hidden border border-gray-800">
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="bg-gray-900"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-[110px_repeat(12,1fr)] items-center">
                            <div class="text-sm text-gray-400 pr-2">35mm Screen</div>
                            <div class="col-span-12 h-14 grid grid-cols-12 rounded-lg overflow-hidden border border-gray-800">
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="bg-gray-900"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-[110px_repeat(12,1fr)] items-center">
                            <div class="text-sm text-gray-400 pr-2">16mm Screen</div>
                            <div class="col-span-12 h-14 grid grid-cols-12 rounded-lg overflow-hidden border border-gray-800">
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="bg-gray-900"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-[110px_repeat(12,1fr)] items-center">
                            <div class="text-sm text-gray-400 pr-2">70mm Screen</div>
                            <div class="col-span-12 h-14 relative rounded-lg overflow-hidden border border-gray-800">
                                <div class="absolute inset-0 grid grid-cols-12">
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="border-r border-gray-800 bg-gray-900"></div>
                                    <div class="bg-gray-900"></div>
                                </div>
                                <div class="absolute inset-y-1 rounded-md border border-amber-400 bg-amber-950/70 px-2 flex items-center text-xs text-amber-200 whitespace-nowrap overflow-hidden" style="left: 52.08%; width: 17.78%;">
                                    6:15 PM &middot; Movie placeholder D
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-[110px_repeat(12,1fr)] items-center">
                            <div class="text-sm text-gray-400 pr-2">IMAX Screen</div>
                            <div class="col-span-12 h-14 grid grid-cols-12 rounded-lg overflow-hidden border border-gray-800">
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="bg-gray-900"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-[110px_repeat(12,1fr)] items-center">
                            <div class="text-sm text-gray-400 pr-2">Drive-In Screen</div>
                            <div class="col-span-12 h-14 grid grid-cols-12 rounded-lg overflow-hidden border border-gray-800">
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="border-r border-gray-800 bg-gray-900"></div>
                                <div class="bg-gray-900"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../../src/components/footer.php'; ?>
</body>
</html>
