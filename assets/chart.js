import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
import am4themes_animated from "@amcharts/amcharts4/themes/animated"

document.addEventListener("DOMContentLoaded", function () {
    fetch('/chart')
        .then(response => response.text())
        .then(jsonData => {
            const data = JSON.parse(jsonData);
            const dataArray = Object.keys(data).map((key) => ({
                key: key,
                value: parseFloat(data[key]),
            }));
            const chartData = dataArray;

            am4core.useTheme(am4themes_animated);
            var chart = am4core.create("chartdiv", am4charts.PieChart);
            // Add data
            chart.data = chartData;
            // Add and configure Series
            var pieSeries = chart.series.push(new am4charts.PieSeries());
            pieSeries.labels.template.text = "{key} [bold]{value.percent.formatNumber('#.0')}%[/]";
            pieSeries.dataFields.value = "value";
            pieSeries.dataFields.category = "key";
            pieSeries.slices.template.tooltipText = "{value.value} Kg/{value.sum} Kg";
            chart.innerRadius = am4core.percent(30);
            pieSeries.slices.template.stroke = am4core.color("#212529");
            pieSeries.slices.template.strokeWidth = 1;
            pieSeries.slices.template.strokeOpacity = 0.7;
        })
});