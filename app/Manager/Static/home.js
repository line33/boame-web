// load chart
document.query('#myChart').then(chart => {

    // get the usuage
    const usage = phpvars.usage.usage;
    
    new Chart(chart, {
        type: 'bar',
        data: {
            labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            datasets: [{
                label: 'App usage frequency',
                data: [
                    Number(usage.monday), 
                    Number(usage.tuesday), 
                    Number(usage.wednesday), 
                    Number(usage.thursday), 
                    Number(usage.friday), 
                    Number(usage.saturday), 
                    Number(usage.sunday)
                ],
                backgroundColor: [
                    '#3370A3',
                    '#3370A3',
                    '#3370A3',
                    '#3370A3',
                    '#3370A3',
                    '#3370A3',
                    '#3370A3',
                ],
                borderWidth: 0
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });

});