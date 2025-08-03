<?php 
include 'includes/config.php';
include 'includes/functions.php';

$tasks = get_all_tasks($conn);
$pending_count = count_tasks($conn, false);
$completed_count = count_tasks($conn, true);
$total_tasks = $pending_count + $completed_count;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Vortex </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#6c5ce7',
                            light: '#a29bfe',
                            dark: '#5649c0'
                        },
                        secondary: '#a29bfe',
                        danger: {
                            DEFAULT: '#ff7675',
                            dark: '#e74c3c'
                        },
                        success: {
                            DEFAULT: '#00b894',
                            dark: '#00997b'
                        },
                        low: '#00cec9',
                        medium: '#0984e3',
                        high: '#d63031',
                        dark: '#1a1a2e',
                        darker: '#16213e'
                    },
                    boxShadow: {
                        'glow': '0 0 15px rgba(108, 92, 231, 0.5)',
                        'glow-success': '0 0 15px rgba(0, 184, 148, 0.5)',
                        'glow-danger': '0 0 15px rgba(255, 118, 117, 0.5)'
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'bounce-slow': 'bounce 2s infinite'
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .priority-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        
        .task-item {
            transition: all 0.3s ease;
        }
        
        .task-item:hover {
            transform: translateY(-2px);
        }
        
        .progress-ring__circle {
            transition: stroke-dashoffset 0.8s ease;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        
        .priority-dropdown {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23a0aec0' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        
        .priority-dropdown:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(108, 92, 231, 0.5);
        }
        
        .dark .priority-dropdown {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23cbd5e0' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        }
        
        .priority-option {
            padding: 0.5rem 1rem;
        }
        
        .priority-option.low {
            background-color: rgba(0, 206, 201, 0.1);
            color: #00cec9;
        }
        
        .priority-option.medium {
            background-color: rgba(9, 132, 227, 0.1);
            color: #0984e3;
        }
        
        .priority-option.high {
            background-color: rgba(214, 48, 49, 0.1);
            color: #d63031;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-purple-900 to-gray-900 min-h-screen p-5 font-sans antialiased dark:bg-gradient-to-br dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    <div class="max-w-3xl mx-auto">
        <!-- action button for dark mode -->
        <button id="theme-toggle" class="fixed bottom-6 right-6 z-50 w-12 h-12 rounded-full bg-primary shadow-lg hover:shadow-glow flex items-center justify-center text-white text-xl transition-all duration-300">
            <i class="fas fa-moon hidden dark:block"></i>
            <i class="fas fa-sun block dark:hidden"></i>
        </button>
        
        <div class="bg-white/5 backdrop-blur-lg rounded-2xl p-8 shadow-2xl border border-white/10 overflow-hidden relative dark:bg-gray-800/50 dark:border-gray-700">
            <!-- Decorative elements -->
            <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-primary/20 animate-pulse-slow"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 rounded-full bg-secondary/20 animate-pulse-slow animation-delay-2000"></div>
            
            <header class="text-center mb-8 relative z-10">
                <h1 class="text-4xl font-bold text-white flex items-center justify-center gap-3 animate__animated animate__fadeInDown dark:text-gray-100">
                    <i class="fas fa-tasks text-primary animate-float"></i> 
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary">Task Vortex</span>
                </h1>
                <p class="text-white/80 mt-2 animate__animated animate__fadeIn animate__delay-1s dark:text-gray-300">Tame your tasks before they overwhelm you</p>
                
                <!-- Progress ring -->
                <div class="mt-6 flex justify-center">
                    <div class="relative w-24 h-24">
                        <svg class="w-full h-full" viewBox="0 0 100 100">
                            <circle class="text-white/10 dark:text-gray-600" stroke-width="8" stroke="currentColor" fill="transparent" r="40" cx="50" cy="50" />
                            <circle 
                                class="progress-ring__circle text-primary" 
                                stroke-width="8" 
                                stroke-linecap="round"
                                stroke="currentColor" 
                                fill="transparent" 
                                r="40" 
                                cx="50" 
                                cy="50"
                                stroke-dasharray="<?= 2 * pi() * 40 ?>"
                                stroke-dashoffset="<?= (1 - ($total_tasks > 0 ? $completed_count / $total_tasks : 0)) * 2 * pi() * 40 ?>"
                            />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center flex-col">
                            <span class="text-2xl font-bold text-white dark:text-gray-100">
                                <?= $total_tasks > 0 ? round(($completed_count / $total_tasks) * 100) : 0 ?>%
                            </span>
                            <span class="text-xs text-white/60 dark:text-gray-400">Complete</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Stats cards -->
            <div class="grid grid-cols-3 gap-4 mb-8 animate__animated animate__fadeIn animate__delay-1s">
                <div class="bg-white/5 rounded-xl p-4 text-center border border-white/10 hover:bg-white/10 transition-all dark:bg-gray-700/50 dark:border-gray-600">
                    <div class="text-3xl font-bold text-white dark:text-gray-100"><?= $total_tasks ?></div>
                    <div class="text-white/70 text-sm dark:text-gray-300">Total Tasks</div>
                </div>
                <div class="bg-white/5 rounded-xl p-4 text-center border border-white/10 hover:bg-white/10 transition-all dark:bg-gray-700/50 dark:border-gray-600">
                    <div class="text-3xl font-bold text-white dark:text-gray-100"><?= $pending_count ?></div>
                    <div class="text-white/70 text-sm dark:text-gray-300">Pending</div>
                </div>
                <div class="bg-white/5 rounded-xl p-4 text-center border border-white/10 hover:bg-white/10 transition-all dark:bg-gray-700/50 dark:border-gray-600">
                    <div class="text-3xl font-bold text-success dark:text-green-400"><?= $completed_count ?></div>
                    <div class="text-success/70 text-sm dark:text-green-300">Completed</div>
                </div>
            </div>

            <form action="add_task.php" method="POST" class="flex gap-3 mb-8 animate__animated animate__fadeIn animate__delay-1s">
                <div class="flex-1 relative">
                    <input 
                        type="text" 
                        name="task" 
                        placeholder="What's on your mind today?" 
                        required
                        class="w-full px-5 py-4 rounded-xl bg-white/10 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-primary focus:shadow-glow transition-all dark:bg-gray-700/50 dark:placeholder-gray-400"
                        autocomplete="off"
                    >
                    <div class="absolute right-3 top-3.5 text-white/30 dark:text-gray-400">
                        <i class="fas fa-keyboard"></i>
                    </div>
                </div>
                <select 
                    name="priority"
                    class="priority-dropdown px-4 py-4 rounded-xl bg-white/10 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:shadow-glow transition-all dark:bg-gray-700/50"
                >
                    <option value="low" class="priority-option low">Low</option>
                    <option value="medium" selected class="priority-option medium">Medium</option>
                    <option value="high" class="priority-option high">High</option>
                </select>
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-primary text-white rounded-xl font-medium hover:bg-primary-dark transition-all hover:shadow-glow flex items-center gap-2 group dark:bg-primary-dark dark:hover:bg-primary"
                >
                    <i class="fas fa-plus transition-transform group-hover:rotate-90"></i> 
                    <span class="hidden sm:inline">Add Task</span>
                </button>
            </form>

            <!-- Task filters -->
            <div class="flex justify-center gap-2 mb-6 animate__animated animate__fadeIn animate__delay-1s">
                <button class="filter-btn px-4 py-2 rounded-lg bg-primary/20 text-white hover:bg-primary/30 transition-all active dark:bg-primary/30" data-filter="all">
                    All <span class="text-xs opacity-70">(<?= $total_tasks ?>)</span>
                </button>
                <button class="filter-btn px-4 py-2 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-all dark:bg-gray-700/50 dark:hover:bg-gray-700" data-filter="pending">
                    Pending <span class="text-xs opacity-70">(<?= $pending_count ?>)</span>
                </button>
                <button class="filter-btn px-4 py-2 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-all dark:bg-gray-700/50 dark:hover:bg-gray-700" data-filter="completed">
                    Completed <span class="text-xs opacity-70">(<?= $completed_count ?>)</span>
                </button>
            </div>

            <!-- Task list -->
            <div class="space-y-3 animate__animated animate__fadeIn animate__delay-1s" id="task-container">
                <?php if (empty($tasks)): ?>
                    <div class="text-center py-10">
                        <i class="fas fa-inbox text-4xl text-white/30 mb-3 dark:text-gray-500"></i>
                        <p class="text-white/60 dark:text-gray-400">No tasks found. Add one above!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                    <div 
                        class="task-item <?= $task['is_completed'] ? 'completed opacity-80' : '' ?> bg-white/5 rounded-xl p-5 flex justify-between items-center border-l-4 
                        <?= $task['priority'] === 'high' ? 'border-high hover:shadow-high/20' : '' ?>
                        <?= $task['priority'] === 'medium' ? 'border-medium hover:shadow-medium/20' : '' ?>
                        <?= $task['priority'] === 'low' ? 'border-low hover:shadow-low/20' : '' ?>
                        transition-all hover:shadow-lg group dark:bg-gray-700/50 dark:hover:bg-gray-700/70"
                        data-priority="<?= $task['priority'] ?>"
                        data-status="<?= $task['is_completed'] ? 'completed' : 'pending' ?>"
                    >
                        <div class="flex items-center">
                            <label class="flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    <?= $task['is_completed'] ? 'checked' : '' ?>
                                    class="hidden complete-checkbox"
                                    data-task-id="<?= $task['id'] ?>"
                                >
                                <span class="w-6 h-6 rounded-full border-2 border-white/30 flex items-center justify-center mr-3 transition-all 
                                    <?= $task['is_completed'] ? 'bg-success border-success' : '' ?> dark:border-gray-400">
                                    <?php if ($task['is_completed']): ?>
                                        <i class="fas fa-check text-white text-xs"></i>
                                    <?php endif; ?>
                                </span>
                            </label>
                            <span class="<?= $task['is_completed'] ? 'line-through text-white/50' : 'text-white' ?> dark:text-gray-100">
                                <?= htmlspecialchars($task['task_text']) ?>
                            </span>
                            <span class="ml-3 px-2.5 py-1 text-xs rounded-full font-medium 
                                <?= $task['priority'] === 'high' ? 'bg-high/20 text-high' : '' ?>
                                <?= $task['priority'] === 'medium' ? 'bg-medium/20 text-medium' : '' ?>
                                <?= $task['priority'] === 'low' ? 'bg-low/20 text-low' : '' ?>
                                flex items-center dark:bg-opacity-30"
                            >
                                <span class="priority-dot 
                                    <?= $task['priority'] === 'high' ? 'bg-high' : '' ?>
                                    <?= $task['priority'] === 'medium' ? 'bg-medium' : '' ?>
                                    <?= $task['priority'] === 'low' ? 'bg-low' : '' ?>
                                "></span>
                                <?= ucfirst($task['priority']) ?>
                            </span>
                        </div>
                        <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <?php if (!$task['is_completed']): ?>
                                <button 
                                    class="complete-btn w-9 h-9 flex items-center justify-center rounded-lg bg-success/10 text-success hover:bg-success/20 hover:shadow-glow-success transition-all dark:bg-success/20 dark:hover:bg-success/30"
                                    data-task-id="<?= $task['id'] ?>"
                                    title="Complete Task"
                                >
                                    <i class="fas fa-check"></i>
                                </button>
                            <?php endif; ?>
                            <button 
                                class="delete-btn w-9 h-9 flex items-center justify-center rounded-lg bg-danger/10 text-danger hover:bg-danger/20 hover:shadow-glow-danger transition-all dark:bg-danger/20 dark:hover:bg-danger/30"
                                data-task-id="<?= $task['id'] ?>"
                                title="Delete Task"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Empty state template (hidden by default) -->
            <div id="empty-state" class="hidden text-center py-10">
                <i class="fas fa-check-circle text-4xl text-success/50 mb-3 dark:text-green-400/50"></i>
                <p class="text-white/60 dark:text-gray-400">No tasks match your filter. Try a different one!</p>
            </div>
        </div>
        
        <footer class="text-center text-white/50 mt-8 text-sm animate__animated animate__fadeIn animate__delay-2s dark:text-gray-500">
            <p>Task Vortex v1.0 &copy; <?= date('Y') ?> | Keep crushing your goals!</p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dark mode toggle
            const themeToggle = document.getElementById('theme-toggle');
            const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
            
            // initial theme
            const currentTheme = localStorage.getItem('theme');
            if (currentTheme) {
                document.documentElement.classList.toggle('dark', currentTheme === 'dark');
            } else {
                document.documentElement.classList.toggle('dark', prefersDarkScheme.matches);
                localStorage.setItem('theme', prefersDarkScheme.matches ? 'dark' : 'light');
            }
            
            themeToggle.addEventListener('click', function() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                
                // SweetAlert 
                if (Swal.isVisible()) {
                    Swal.close();
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Theme changed',
                            text: `Switched to ${isDark ? 'dark' : 'light'} mode`,
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false,
                            background: isDark ? '#1a1a2e' : '#fff',
                            color: isDark ? '#fff' : '#000'
                        });
                    }, 100);
                }
            });
            
            // Task filteration 
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active', 'bg-primary/20', 'dark:bg-primary/30'));
                    btn.classList.add('active', 'bg-primary/20', 'dark:bg-primary/30');
                    
                    const filter = btn.dataset.filter;
                    const tasks = document.querySelectorAll('.task-item');
                    let visibleTasks = 0;
                    
                    tasks.forEach(task => {
                        const isCompleted = task.dataset.status === 'completed';
                        const shouldShow = 
                            filter === 'all' || 
                            (filter === 'pending' && !isCompleted) || 
                            (filter === 'completed' && isCompleted);
                        
                        if (shouldShow) {
                            task.style.display = 'flex';
                            visibleTasks++;
                        } else {
                            task.style.display = 'none';
                        }
                    });
                    
                    const emptyState = document.getElementById('empty-state');
                    const taskContainer = document.getElementById('task-container');
                    
                    if (visibleTasks === 0) {
                        emptyState.classList.remove('hidden');
                        taskContainer.classList.add('hidden');
                    } else {
                        emptyState.classList.add('hidden');
                        taskContainer.classList.remove('hidden');
                    }
                });
            });
            
            // Task completion 
            function handleTaskCompletion(taskId, taskItem) {
                fetch(`complete_task.php?id=${taskId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Update task appearance
                            taskItem.classList.add('completed', 'opacity-80');
                            taskItem.dataset.status = 'completed';
                            
                            // Update checkbox
                            const checkbox = taskItem.querySelector('.complete-checkbox');
                            if (checkbox) checkbox.checked = true;
                            
                            // Update text
                            const taskText = taskItem.querySelector('span:not(.priority-dot)');
                            if (taskText) {
                                taskText.classList.add('line-through', 'text-white/50');
                                taskText.classList.remove('text-white', 'dark:text-gray-100');
                            }
                            
                            // complete button removal
                            const completeBtn = taskItem.querySelector('.complete-btn');
                            if (completeBtn) completeBtn.remove();
                            
                            // Animation
                            taskItem.style.transform = 'scale(0.98)';
                            setTimeout(() => {
                                taskItem.style.transform = '';
                            }, 300);
                            
                            // Update counters
                            updateTaskCounters(-1, 1);
                            
                            // success message
                            const isDark = document.documentElement.classList.contains('dark');
                            Swal.fire({
                                title: 'Task Completed!',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false,
                                background: isDark ? '#1a1a2e' : '#fff',
                                color: isDark ? '#fff' : '#000'
                            });
                        } else {
                            throw new Error(data.message || 'Unknown error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        const isDark = document.documentElement.classList.contains('dark');
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to complete task. Please try again.',
                            icon: 'error',
                            background: isDark ? '#1a1a2e' : '#fff',
                            color: isDark ? '#fff' : '#000'
                        });
                    });
            }
            
            // counters
            function updateTaskCounters(pendingChange, completedChange) {
                const pendingCountElement = document.querySelector('[data-filter="pending"] span');
                const completedCountElement = document.querySelector('[data-filter="completed"] span');
                const totalCountElement = document.querySelector('[data-filter="all"] span');
                
                if (pendingCountElement) {
                    const current = parseInt(pendingCountElement.textContent.match(/\d+/)[0]);
                    pendingCountElement.textContent = `(${current + pendingChange})`;
                }
                
                if (completedCountElement) {
                    const current = parseInt(completedCountElement.textContent.match(/\d+/)[0]);
                    completedCountElement.textContent = `(${current + completedChange})`;
                }
            }
            
            document.addEventListener('click', function(e) {
                // Complete button 
                if (e.target.closest('.complete-btn') || e.target.closest('.complete-checkbox')) {
                    const btn = e.target.closest('.complete-btn') || e.target.closest('.complete-checkbox');
                    const taskId = btn.dataset.taskId;
                    const taskItem = btn.closest('.task-item');
                    
                    if (!taskItem.classList.contains('completed')) {
                        handleTaskCompletion(taskId, taskItem);
                    }
                }
                
                // Delete button
                if (e.target.closest('.delete-btn')) {
                    const btn = e.target.closest('.delete-btn');
                    const taskId = btn.dataset.taskId;
                    const taskItem = btn.closest('.task-item');
                    const isDark = document.documentElement.classList.contains('dark');
                    const isCompleted = taskItem.classList.contains('completed');
                    
                    Swal.fire({
                        title: 'Delete this task?',
                        text: "You won't be able to undo this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d63031',
                        cancelButtonColor: '#6c5ce7',
                        confirmButtonText: 'Yes, delete it!',
                        background: isDark ? '#1a1a2e' : '#fff',
                        color: isDark ? '#fff' : '#000'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`delete_task.php?id=${taskId}`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Animate deletion
                                        taskItem.style.transform = 'translateX(100%)';
                                        taskItem.style.opacity = '0';
                                        
                                        setTimeout(() => {
                                            taskItem.remove();
                                            
                                            updateTaskCounters(isCompleted ? 0 : -1, isCompleted ? -1 : 0);
                                            
                                            if (document.querySelectorAll('.task-item').length === 0) {
                                                document.getElementById('empty-state').classList.remove('hidden');
                                            }
                                        }, 300);
                                    }
                                })
                                .catch(error => console.error('Error:', error));
                        }
                    });
                }
            });
            
            if (window.location.search.includes('task_added=true')) {
                const lastTask = document.querySelector('.task-item:last-child');
                if (lastTask) {
                    lastTask.classList.add('animate__animated', 'animate__fadeInUp');
                    setTimeout(() => {
                        lastTask.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 100);
                }
            }
        });
    </script>
</body>
</html>