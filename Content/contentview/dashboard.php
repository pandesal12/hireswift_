<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../Query/DashboardData.php';

// Get current user from session (adjust based on your authentication system)
$currentUser = $_SESSION['email'] ?? $_SESSION['user_id'] ?? null;

// If no user is logged in, redirect or handle appropriately
// if (!$currentUser) {
//     // Redirect to login page or handle unauthenticated user
//     header('Location: ../login.php');
//     exit();
// }

// Initialize dashboard data with current user
$dashboardData = new DashboardData($currentUser);

// Get real data from database (now filtered by user)
$totalApplications = $dashboardData->getTotalApplications();
$shortlistedApplications = $dashboardData->getApplicationsByStatus('Shortlisted');
$newApplications = $dashboardData->getNewApplications();
$averageScore = $dashboardData->getAverageScore();

// Get growth percentages
$totalGrowth = $dashboardData->getGrowthPercentage('total');
$shortlistedGrowth = $dashboardData->getGrowthPercentage('shortlisted');
$newGrowth = $dashboardData->getGrowthPercentage('new');

// Get chart data
$trendData = $dashboardData->getApplicationsTrend();
$jobApplications = $dashboardData->getApplicationsPerJob();
$statusData = $dashboardData->getStatusDistribution();
$recentApplications = $dashboardData->getRecentApplications(5);

// Fill missing dates in trend data
$completeTrendData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $found = false;
    foreach ($trendData as $data) {
        if ($data['date'] == $date) {
            $completeTrendData[] = $data;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $completeTrendData[] = ['date' => $date, 'applications' => 0];
    }
}
$trendData = $completeTrendData;

// Debug information (remove in production)
// echo "Current user: " . htmlspecialchars($currentUser) . "<br>";
// echo "Total applications for this user: " . $totalApplications . "<br>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications Dashboard</title>
    <link rel="stylesheet" href="./CSS/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>

    </style>
</head>
<body>

<div class="dashboard-content">
    <div class="page-header">
        <h1>Applications Dashboard</h1>
        <p>Overview of your job applications analytics</p>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Applications</span>
                <div class="stat-icon blue">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($totalApplications); ?></div>
            <div class="stat-change">
                <i class="fas fa-arrow-<?php echo $totalGrowth >= 0 ? 'up' : 'down'; ?>"></i> 
                <?php echo ($totalGrowth >= 0 ? '+' : '') . $totalGrowth; ?>% from last month
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Shortlisted</span>
                <div class="stat-icon green">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($shortlistedApplications); ?></div>
            <div class="stat-change">
                <i class="fas fa-arrow-<?php echo $shortlistedGrowth >= 0 ? 'up' : 'down'; ?>"></i> 
                <?php echo ($shortlistedGrowth >= 0 ? '+' : '') . $shortlistedGrowth; ?>% from last month
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">New Applications</span>
                <div class="stat-icon orange">
                    <i class="fas fa-plus-circle"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($newApplications); ?></div>
            <div class="stat-change">
                <i class="fas fa-arrow-<?php echo $newGrowth >= 0 ? 'up' : 'down'; ?>"></i> 
                <?php echo ($newGrowth >= 0 ? '+' : '') . $newGrowth; ?>% from yesterday
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Average Score</span>
                <div class="stat-icon purple">
                    <i class="fas fa-star"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo $averageScore ?: '0.00'; ?></div>
            <div class="stat-change">
                <i class="fas fa-chart-line"></i> Out of 100
            </div>
        </div>
    </div>

    <!-- Trend Chart -->
    <div class="chart-container">
        <h2 class="chart-title">Applications Trend (Last 7 Days)</h2>
        <div class="chart-wrapper">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="chart-grid">
        <div class="chart-container">
            <h2 class="chart-title">Applications per Job</h2>
            <div class="chart-wrapper">
                <canvas id="jobChart"></canvas>
            </div>
            <?php if (empty($jobApplications)): ?>
                <div class="no-data-message">
                    <p>No job applications data available</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="chart-container">
            <h2 class="chart-title">Application Status Distribution</h2>
            <div class="chart-wrapper">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Applications Table -->
    <div class="chart-container">
        <h2 class="chart-title">Recent Applications</h2>
        <div class="table-responsive">
            <table class="recent-applications-table">
                <thead>
                    <tr>
                        <th>Applicant Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Applied Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentApplications as $application): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($application['applicant_name']); ?></td>
                        <td><?php echo htmlspecialchars($application['email']); ?></td>
                        <td><?php echo htmlspecialchars($application['phone']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                                <?php echo htmlspecialchars($application['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $application['score']; ?></td>
                        <td><?php echo date('M j, Y', strtotime($application['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Debug: Check if Chart.js loaded
console.log('Chart.js loaded:', typeof Chart !== 'undefined');

// Store chart instances
let chartInstances = {};

// Get responsive settings
function getResponsiveSettings() {
    const isMobile = window.innerWidth < 768;
    const isSmallMobile = window.innerWidth < 480;
    return {
        isMobile,
        isSmallMobile,
        fontSize: isSmallMobile ? 10 : isMobile ? 11 : 12,
        legendDisplay: !isSmallMobile,
        legendPosition: isMobile ? 'top' : 'bottom'
    };
}

// Initialize charts when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing charts...');
    
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }

    const settings = getResponsiveSettings();

    // Trend Chart Data
    const trendLabels = <?php echo json_encode(array_map(function($item) {
        return date('M j', strtotime($item['date']));
    }, $trendData)); ?>;
    
    const trendValues = <?php echo json_encode(array_column($trendData, 'applications')); ?>;
    
    console.log('Trend data:', { labels: trendLabels, values: trendValues });

    // Create Trend Chart
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        chartInstances.trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'New Applications',
                    data: trendValues,
                    borderColor: '#4285f4',
                    backgroundColor: 'rgba(66, 133, 244, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: settings.isMobile ? 3 : 4,
                    pointHoverRadius: settings.isMobile ? 5 : 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: settings.fontSize
                        },
                        bodyFont: {
                            size: settings.fontSize
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f3f4'
                        },
                        ticks: {
                            font: {
                                size: settings.fontSize
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: settings.fontSize
                            },
                            maxRotation: settings.isMobile ? 45 : 0
                        }
                    }
                }
            }
        });
        console.log('Trend chart created');
    }

    // Job Applications Chart
    <?php if (!empty($jobApplications)): ?>
    const jobLabels = <?php echo json_encode(array_column($jobApplications, 'job')); ?>;
    const jobValues = <?php echo json_encode(array_column($jobApplications, 'applications')); ?>;
    
    console.log('Job data:', { labels: jobLabels, values: jobValues });

    const jobCtx = document.getElementById('jobChart');
    if (jobCtx) {
        chartInstances.jobChart = new Chart(jobCtx, {
            type: 'bar',
            data: {
                labels: jobLabels,
                datasets: [{
                    label: 'Applications',
                    data: jobValues,
                    backgroundColor: [
                        '#4285f4',
                        '#34a853',
                        '#ff9800',
                        '#9c27b0',
                        '#f44336',
                        '#00bcd4',
                        '#795548'
                    ],
                    borderRadius: settings.isMobile ? 4 : 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: settings.fontSize
                        },
                        bodyFont: {
                            size: settings.fontSize
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f3f4'
                        },
                        ticks: {
                            font: {
                                size: settings.fontSize
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: settings.fontSize
                            },
                            maxRotation: settings.isMobile ? 45 : 0
                        }
                    }
                }
            }
        });
        console.log('Job chart created');
    }
    <?php endif; ?>

    // Status Chart
    const statusLabels = <?php echo json_encode(array_column($statusData, 'status')); ?>;
    const statusValues = <?php echo json_encode(array_column($statusData, 'count')); ?>;
    
    console.log('Status data:', { labels: statusLabels, values: statusValues });

    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        chartInstances.statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: [
                        '#ff9800',
                        '#34a853',
                        '#f44336',
                        '#2196f3',
                        '#9c27b0',
                        '#00bcd4',
                        '#795548'
                    ],
                    borderWidth: 0,
                    hoverOffset: settings.isMobile ? 5 : 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: settings.legendDisplay,
                        position: settings.legendPosition,
                        labels: {
                            padding: settings.isMobile ? 10 : 20,
                            usePointStyle: true,
                            font: {
                                size: settings.fontSize
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: settings.fontSize
                        },
                        bodyFont: {
                            size: settings.fontSize
                        }
                    }
                }
            }
        });
        console.log('Status chart created');
    }

    console.log('All charts initialized:', chartInstances);
});

// Handle window resize with debouncing
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        const newSettings = getResponsiveSettings();
        
        Object.values(chartInstances).forEach(chart => {
            if (chart) {
                // Update font sizes
                if (chart.options.scales && chart.options.scales.x) {
                    chart.options.scales.x.ticks.font.size = newSettings.fontSize;
                }
                if (chart.options.scales && chart.options.scales.y) {
                    chart.options.scales.y.ticks.font.size = newSettings.fontSize;
                }
                
                chart.options.plugins.tooltip.titleFont.size = newSettings.fontSize;
                chart.options.plugins.tooltip.bodyFont.size = newSettings.fontSize;
                
                // Update legend for status chart
                if (chart === chartInstances.statusChart) {
                    chart.options.plugins.legend.display = newSettings.legendDisplay;
                    chart.options.plugins.legend.position = newSettings.legendPosition;
                    chart.options.plugins.legend.labels.font.size = newSettings.fontSize;
                }
                
                chart.update('resize');
                chart.resize();
            }
        });
    }, 150);
});

// Auto-refresh dashboard every 5 minutes
setTimeout(function() {
    location.reload();
}, 300000);
</script>

</body>
</html>