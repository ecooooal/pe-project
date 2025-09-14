let courses = JSON.parse(document.getElementById('reports_courses_data').dataset.courses);
let exam_questions = JSON.parse(document.getElementById('reports_questions_data').dataset.questions);
let exam_students = JSON.parse(document.getElementById('reports_students_data').dataset.students);
let overall_exam_scores = JSON.parse(document.getElementById('reports_exam_scores_data').dataset.examScores);
let exam_groups_scores = JSON.parse(document.getElementById('reports_exam_groups_data').dataset.examGroups);
let level_scores = JSON.parse(document.getElementById('reports_level_scores_data').dataset.levelScores);
let exam_compare_types_and_blooms_data = JSON.parse(document.getElementById('reports_type_with_level_scores_data').dataset.typeScores);
let with_groups = courses.length > 1 ? true : false; 
let subplot_rows = with_groups ? 2 : 1;
const group_colors = ['rgb(49,130,189)', 'rgb(87, 196, 133)', 'rgb(238, 99, 99)', 'rgb(120,130,80)'];

function renderHistogram(overall, course_groups){
    let histogram_traces = [];
    // Trace for histogram
    let overall_histogram_trace = {
        x: overall.map(item => item.points),
        name: 'Overall',  
        type: 'histogram',
        xbins: { size: 5 },
        marker: {
        line: { width: 2, color: '#333'},
        color: 'rgb(49,130,189)',
        }
    };
    histogram_traces.push(overall_histogram_trace);

    const groups = Object.keys(course_groups);

    groups.forEach((group, idx) => {
        let trace = {
            x: course_groups[group]['group_exam_score'].map(item => item.points),
            type: 'histogram',
            name: group,  
            xbins: { size: 5 },
            xaxis: 'x2',
            yaxis: 'y2',
            marker: {
                line: { width: 2, color: '#333' },
                color: group_colors[idx % group_colors.length],  
                opacity: .7
            }
        };
        histogram_traces.push(trace);
    });

    let histogram_layout = {
      grid: {rows:subplot_rows, columns:1, pattern: 'independent'},
      title: {
        text: "Exam Scores Distribution",
        font: { size: 16, color: "#4B5563", family: "Arial Black, sans-serif" },
        x: 0.55,
        xanchor: 'center',
        yanchor: 'top'
      },
      barmode: "stack",
      height: 400,
      autosize: true,
      margin: { l: 40, r: 10, t: 80, b: 30 },
      xaxis: {
        tickfont: { size: 12, color: 'rgb(107, 107, 107)' },
        showticklabels: false,
      },
      yaxis: {
        dtick: 1,
        domain: [0.55, 1],
        title: { text: 'Frequency', font: { size: 12, color: '#7f7f7f' } },
        tickfont: { size: 12, color: 'rgb(107, 107, 107)' },
      },
      xaxis2: {
        dtick:10,
        title: { text: 'Exam Scores', font: { size: 12, color: '#7f7f7f' },  standoff: 10 },
        tickfont: { size: 12, color: 'rgb(107, 107, 107)' },
        anchor: 'y2'
      },
      yaxis2: {
        dtick: 1,
        domain: [0, 0.50],
        title: { text: 'Frequency', font: { size: 12, color: '#7f7f7f' } },
        tickfont: { size: 12, color: 'rgb(107, 107, 107)' }
      },
    legend: {
        orientation: 'h',
        x: 0.5,
        xanchor: 'center',
        y: 1.0,          // slightly above plot area
        yanchor: 'bottom'
    }
    };

    Plotly.newPlot('exam-histogram', histogram_traces, histogram_layout).then(() => {
            Plotly.Plots.resize('exam-histogram');  
        });

}

function renderBoxPlot(overall, course_groups){
    let boxplot_traces = [];

    const overall_boxplot_hover_texts = overall.map(item => [item.Name, item.Subject, item.topic]);
    let overall_boxplot_trace = {
        y: overall.map(item => item.points),
        name: 'Overall',  
        type: 'box',
        boxpoints: 'all',
        customdata: overall_boxplot_hover_texts,
        hovertemplate:
        'Name: %{customdata[0]}<br>' +
        'Points: %{y}<br>' +
        'Subject: %{customdata[1]}<br>' +
        'Topic: %{customdata[2]}<extra></extra>',
        jitter: 0.8,
        whiskerwidth: 0.4,
        pointpos: -2,
        xaxis: 'x1',
        yaxis: 'y1',
        marker: {
            color: 'rgb(49,130,189)',
            size: 5,
            line: { width: 1, color: '#333' },
            opacity: 0.7
        },    
        };
    boxplot_traces.push(overall_boxplot_trace);
    const groups = Object.keys(course_groups);

    const groups_boxplot_hover_texts = groups.map(item => [item.Name, item.Subject, item.topic]);
    groups.forEach((group, idx) => {
        let trace = {
            y: course_groups[group]['group_exam_score'].map(item => item.points),
            type: 'box',
            name: group,  
            boxpoints: 'all',
            customdata: groups_boxplot_hover_texts,
            hovertemplate:
            'Name: %{customdata[0]}<br>' +
            'Points: %{y}<br>' +
            'Subject: %{customdata[1]}<br>' +
            'Topic: %{customdata[2]}<extra></extra>',
            jitter: 0.8,
            whiskerwidth: 0.4,
            pointpos: -2,
            xaxis: 'x2',
            yaxis: 'y2',
            marker: {
                size: 5,
                line: { width: 1, color: '#333' },
                color: group_colors[idx % group_colors.length],  
                opacity: 0.7
            },
            opacity: 1
        };
        boxplot_traces.push(trace);
    });

    let boxplot_layout = {
      grid: {rows:subplot_rows, columns:1, pattern: 'independent'},
      title: {
        text: "Exam Scores Box Plots",
        font: { size: 16, color: "#4B5563", family: "Arial Black, sans-serif" },
        x: 0.55,
        xanchor: 'center',
        yanchor: 'top'
      },
      height: 500,
      autosize: true,
     margin: { l: 60, r: 10, t: 80, b: 30 },
      yaxis: {
        autorange: true,
        dtick: 10,
        gridwidth: 1,
        title: { text: 'Exam Scores', font: { size: 12, color: '#7f7f7f' }},
        tickfont: { size: 12, color: 'rgb(107, 107, 107)' },
      },
      xaxis: {
        domain: [0, 1],
        tickfont: { size: 12, color: 'rgb(107, 107, 107)' },
      },
      yaxis2: {
        title: { text: 'Exam Scores', font: { size: 12, color: '#7f7f7f' }},
        domain: [0, 0.50],
        autorange: true,
        dtick: 10,
        gridwidth: 1,
        tickfont: { size: 12, color: 'rgb(107, 107, 107)' },
        anchor: 'y2'
      },
      xaxis2: {
        type: 'category',
        tickfont: { size: 12, color: 'rgb(107, 107, 107)' }
      },
    legend: {
        orientation: 'h',
        x: 0.5,
        xanchor: 'center',
        y: 1.0,          // slightly above plot area
        yanchor: 'bottom'
    }
    };

    Plotly.newPlot('exam-boxplot', boxplot_traces, boxplot_layout).then(() => {
            Plotly.Plots.resize('exam-boxplot'); 
        });

}

function renderBarChartCompareTypesWithLevels(data){
     const bloom_colors = {
      'Remember': 'rgb(49,130,189)',
      'Understand': 'rgb(87, 196, 133)',
      'Apply': 'rgb(252, 186, 3)',
      'Analyze': 'rgb(245, 175, 75)',
      'Evaluate' : 'rgb(238, 99, 99)',
      'Create' : 'rgb(158, 120, 242)'
    };

    const exam_compare_types_and_blooms_traces = [];

    data.forEach(qtype => {
        Object.entries(qtype.blooms).forEach(([bloom, { raw, normalized }]) => {
            const xValues = courses.map(course => `${course}<br>${qtype.name}`);
            const hoverTexts = raw.map(
                (val, i) => `
                ${courses[i]}: ${val.toFixed(2)}<br>
                normalized score: ${normalized[i]}`
            );

            exam_compare_types_and_blooms_traces.push({
                x: xValues,
                y: raw,
                name: bloom,
                type: 'bar',
                text: hoverTexts,
                hoverinfo: 'text',    textposition: 'none',  // <-- hides text inside bars

                marker: {
                    color: bloom_colors[bloom] || '#888',
                    line: { width: 2, color: '#333' },
                    opacity: 0.7
                },
                legendgroup: bloom,
                showlegend: !exam_compare_types_and_blooms_traces.some(t => t.name === bloom),
            });
        });
    });

    const exam_compare_types_and_blooms_layout = {
        title: {
            text: "Compare by Question Types with Bloom's Level",
            font: { size: 16, color: "#4B5563", family: "Arial Black, sans-serif" },
            x: 0.50,
            xanchor: 'center',
            yanchor: 'top'
        },
        height: 500,
        autosize: true,
        barmode: 'stack',
        margin: { l: 60, r: 10, t: 80, b: 80 },
        hovermode: 'closest',
        yaxis: {
            gridwidth: 1,
            title: { text: 'Normalized Exam Scores', font: { size: 12, color: '#7f7f7f' }},
            tickfont: { size: 12, color: 'rgb(107, 107, 107)' },
        },
        xaxis: {
            domain: [0, 1],
            tickfont: { size: 12, color: 'rgb(107, 107, 107)' },
            tickangle: -30
        }, 
        legend: {
            orientation: 'h',
            x: 0.5,
            xanchor: 'center',
            y: 1.0,         
            yanchor: 'bottom'
        }
    };

    Plotly.newPlot('exam-compare-group', exam_compare_types_and_blooms_traces, exam_compare_types_and_blooms_layout).then(() => {
            Plotly.Plots.resize('exam-compare-group'); 
        });
}

function renderHeatStripQuestions(data){
    var question_heatmap_traces = Object.keys(data).map(level => {
        let qData = data[level].sort((a, b) => a.average_percentage - b.average_percentage);
            return {
            y: Array(qData.length).fill(level),
            x: Array(qData.length).fill(1),
            type: 'bar',
            orientation: 'h',
            marker: {
                line: { width: 2.1, color: '#333', opacity:0.7},
                color: qData.map(q => q.average_percentage),
                colorscale: [
                    ['0.0', 'rgb(49, 130, 189)'],
                    ['0.1', 'rgb(69, 135, 179)'],
                    ['0.2', 'rgb(89, 140, 169)'],
                    ['0.3', 'rgb(109, 145, 159)'],
                    ['0.4', 'rgb(129, 150, 149)'],
                    ['0.5', 'rgb(149, 155, 139)'],
                    ['0.6', 'rgb(169, 160, 129)'],
                    ['0.7', 'rgb(189, 165, 119)'],
                    ['0.8', 'rgb(209, 170, 109)'],
                    ['0.9', 'rgb(229, 175, 99)'],
                    ['1.0', 'rgb(245, 175, 75)']
                ],
                opacity: 1,
                cmin: 0,
                cmax: 100,
                colorbar: { title: 'Bloom Score %' }
            },
            customdata: qData.map(q => [q.text, q.type, q.average_score, q.average_percentage, q.max_score_attainable, q.topic, q.subject]),
            hovertemplate:
                "Question text: %{customdata[0]}<br>" +
                "Question type: %{customdata[1]}<br>" +
                "Average score: %{customdata[2]}<br>" +
                "Average score in Percent: %{customdata[3]}%<br>" +
                "Max Score Attainable: %{customdata[4]}<br>"  +
                "Question topic: %{customdata[5]}<br>" +
                "Question subject: %{customdata[6]}<extra></extra>",
            hoverlabel: {
                font: { color: 'white' }      // hover text color
            },
            showlegend: false
            };
    });

    var question_heatmap_layout = {
    barmode: 'stack',
        title: {
        text: "Performance Distribution by Question Levels ",
        font: { size: 16, color: "#4B5563", family: "Arial Black, sans-serif" },
        x: 0.5,
        xanchor: 'center'
    },
    width: 900,
    height: 400,
    xaxis: {  
    showgrid: false, 
    zeroline: false, 
    title: {
        text: 'No. of Questions',
        font: { size: 12, color: '#7f7f7f' }
        }
    },
    yaxis: { 
    tickfont: {
        size: 12,
        color: 'rgb(107, 107, 107)',
    },
    categoryorder: "array",
    categoryarray: ["Create", "Evaluate", "Analyze", "Apply", "Understand", "Remember"],
    },
    autosize: true,
        margin: { l: 70, r: 10, t: 60, b: 150 },
        annotations: [
    {
        text: "Average Scores <br> in Percent",
        xref: "paper",
        yref: "paper",
        x: 1.05,       // align with colorbar (to the right of plot)
        y: 0,      // below the plot area
        showarrow: false,
        textangle: 0, // rotate text
        font: {
            size: 10,
            color: 'rgb(107, 107, 107)'
        },
        xanchor: "center",
        yanchor: "top"
    }
    ]
    };

    Plotly.newPlot('exam-heatmap', question_heatmap_traces, question_heatmap_layout).then(() => {
            Plotly.Plots.resize('exam-heatmap');  
        });

}

document.addEventListener('DOMContentLoaded', () => {
    const histogram = document.getElementById('exam-histogram');
    const boxplot = document.getElementById('exam-boxplot');
    const compare_type = document.getElementById('exam-compare-group');
    const heatmap_questions = document.getElementById('exam-heatmap');

    if (histogram) {
        renderHistogram(overall_exam_scores, exam_groups_scores);
    }
    if (boxplot) {
        renderBoxPlot(overall_exam_scores, exam_groups_scores);
    }
    if (compare_type) {
        renderBarChartCompareTypesWithLevels(exam_compare_types_and_blooms_data);
    }
    if (heatmap_questions) {
        renderHeatStripQuestions(level_scores);
    }
});