// ApexCharts integration
// This file will be lazy-loaded only on pages that need charts

export async function initLineTrendChart(element, data) {
    const ApexCharts = (await import('apexcharts')).default;
    
    const options = {
        series: data.series || [],
        chart: {
            type: 'line',
            height: 350,
            animations: { enabled: true, speed: 1500 },
            toolbar: { show: false }
        },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#10b981', '#f59e0b', '#ef4444'],
        xaxis: {
            categories: data.categories || []
        },
        yaxis: {
            title: { text: 'Jumlah Siswa' }
        },
        legend: {
            position: 'bottom'
        }
    };
    
    const chart = new ApexCharts(element, options);
    await chart.render();
    return chart;
}

export async function initDonutStatusChart(element, data) {
    const ApexCharts = (await import('apexcharts')).default;
    
    const options = {
        series: data.series || [],
        chart: {
            type: 'donut',
            height: 300
        },
        labels: data.labels || [],
        colors: ['#10b981', '#f59e0b', '#ef4444', '#3b82f6'],
        legend: { position: 'bottom' }
    };
    
    const chart = new ApexCharts(element, options);
    await chart.render();
    return chart;
}

export async function initBarClassChart(element, data) {
    const ApexCharts = (await import('apexcharts')).default;
    
    const options = {
        series: [{ name: 'Percentage', data: data.data || [] }],
        chart: {
            type: 'bar',
            height: 350,
            horizontal: true
        },
        xaxis: {
            categories: data.categories || []
        },
        colors: ['#3b82f6']
    };
    
    const chart = new ApexCharts(element, options);
    await chart.render();
    return chart;
}
