$(function () {
    $.get("index.php?option=com_pesapal&task=trends.getDonationStats&tmpl=json", function( result ) {

        result = jQuery.parseJSON(result).data;
        console.log(result);
        Highcharts.chart('container', {

            title: {
                text: 'Donation Trends Overtime'
            },

            subtitle: {
                text: 'Source: pesapal.com'
            },

            yAxis: {
                title: {
                    text: 'Donation Amount'
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle'
            },
            xAxis:{
                categories:result.categories
            },
            series: result.series
        });
    });

    $.get( "index.php?option=com_pesapal&task=trends.getStatusStats&tmpl=json", function( result ) {
        result=jQuery.parseJSON(result);
        var data = result.map(function(item){
            return {name:item.status,y:parseInt(item.total)}
        });

        Highcharts.chart('container_pie', {
            title: {
                text: 'Donation Status'
            },

            subtitle: {
                text: 'Source: pesapal.com'
            },

            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    dataLabels: {
                        enabled: true,
                        distance: -50,
                        style: {
                            fontWeight: 'bold',
                            color: 'white'
                        }
                    },

                }
            },
            series: [{
                type: 'pie',
                name: 'Status',
                data: data
            }]
        });
    });
});

