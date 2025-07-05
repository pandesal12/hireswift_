<?php
// Sample data - replace with actual database queries
$totalApplicants = 1247;
$acceptedApplicants = 89;
$newApplicants = 23;

// Sample data for charts
$trendData = [
    ['date' => '2024-01-01', 'applicants' => 45],
    ['date' => '2024-01-02', 'applicants' => 52],
    ['date' => '2024-01-03', 'applicants' => 38],
    ['date' => '2024-01-04', 'applicants' => 67],
    ['date' => '2024-01-05', 'applicants' => 71],
    ['date' => '2024-01-06', 'applicants' => 58],
    ['date' => '2024-01-07', 'applicants' => 63]
];

$jobOpenings = [
    ['job' => 'Software Engineer', 'applicants' => 45],
    ['job' => 'Data Analyst', 'applicants' => 32],
    ['job' => 'UI/UX Designer', 'applicants' => 28],
    ['job' => 'Project Manager', 'applicants' => 19],
    ['job' => 'DevOps Engineer', 'applicants' => 15]
];

$statusData = [
    ['status' => 'Pending', 'count' => 856],
    ['status' => 'Accepted', 'count' => 89],
    ['status' => 'Rejected', 'count' => 302]
];
?>
<!-- Access from master root -->
<link rel="stylesheet" href="CSS/dashboard.css">
<div class="page-header">
    <h1 style="color: #212529; margin-bottom: 8px;">Dashboard</h1>
    <p style="color: #6c757d; margin-bottom: 32px;">Overview of your recruitment analytics</p>
</div>

<!-- Statistics Cards -->
<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Total Applicants</span>
            <div class="stat-icon blue">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($totalApplicants); ?></div>
        <div class="stat-change">
            <i class="fas fa-arrow-up"></i> +12% from last month
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Accepted Applicants</span>
            <div class="stat-icon green">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($acceptedApplicants); ?></div>
        <div class="stat-change">
            <i class="fas fa-arrow-up"></i> +8% from last month
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">New Applicants</span>
            <div class="stat-icon orange">
                <i class="fas fa-user-plus"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($newApplicants); ?></div>
        <div class="stat-change">
            <i class="fas fa-arrow-up"></i> +15% from yesterday
        </div>
    </div>
</div>

<!-- Trend Chart -->
<div class="chart-container">
    <h2 class="chart-title">Applicants Trend (Last 7 Days)</h2>
    <canvas id="trendChart" width="400" height="200"></canvas>
</div>

<!-- Charts Grid -->
<div class="chart-grid">
    <div class="chart-container">
        <h2 class="chart-title">Applicants per Job Opening</h2>
        <canvas id="jobChart" width="400" height="300"></canvas>
    </div>

    <div class="chart-container">
        <h2 class="chart-title">Application Status Distribution</h2>
        <canvas id="statusChart" width="400" height="300"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Trend Line Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');
const trendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($trendData, 'date')); ?>,
        datasets: [{
            label: 'New Applicants',
            data: <?php echo json_encode(array_column($trendData, 'applicants')); ?>,
            borderColor: '#4285f4',
            backgroundColor: 'rgba(66, 133, 244, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#f1f3f4'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Job Openings Bar Chart
const jobCtx = document.getElementById('jobChart').getContext('2d');
const jobChart = new Chart(jobCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($jobOpenings, 'job')); ?>,
        datasets: [{
            label: 'Applicants',
            data: <?php echo json_encode(array_column($jobOpenings, 'applicants')); ?>,
            backgroundColor: [
                '#4285f4',
                '#34a853',
                '#ff9800',
                '#9c27b0',
                '#f44336'
            ],
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#f1f3f4'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Status Pie Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($statusData, 'status')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($statusData, 'count')); ?>,
            backgroundColor: [
                '#ff9800',
                '#34a853',
                '#f44336'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        }
    }
});
</script>
