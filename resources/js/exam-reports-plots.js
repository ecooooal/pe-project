let courses = JSON.parse(document.getElementById('reports_courses_data').dataset.courses);
let exam_questions = JSON.parse(document.getElementById('reports_questions_data').dataset.questions);
let exam_students = JSON.parse(document.getElementById('reports_students_data').dataset.students);
let with_groups = courses.length > 1 ? true : false; 
let subplot_rows = with_groups ? 2 : 1;

let overall_exam_scores = JSON.parse(document.getElementById('reports_exam_scores_data').dataset.examScores);
let exam_groups_scores = JSON.parse(document.getElementById('reports_exam_groups_data').dataset.examGroups);
let level_scores = JSON.parse(document.getElementById('reports_level_scores_data').dataset.levelScores);
let exam_compare_types_and_blooms_data = JSON.parse(document.getElementById('reports_type_with_level_scores_data').dataset.typeScores);

const group_colors = ['rgb(49,130,189)', 'rgb(87, 196, 133)', 'rgb(238, 99, 99)', 'rgb(120,130,80)'];
     const bloom_colors = {
      'Remember': 'rgb(49,130,189)',
      'Understand': 'rgb(87, 196, 133)',
      'Apply': 'rgb(252, 186, 3)',
      'Analyze': 'rgb(245, 175, 75)',
      'Evaluate' : 'rgb(238, 99, 99)',
      'Create' : 'rgb(158, 120, 242)'
    };
function renderHistogram(overall, course_groups){
    let histogram_traces = [];
    // Trace for histogram
    if (with_groups){
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
    }


    const groups = Object.keys(course_groups);

    groups.forEach((group, idx) => {
        let trace = {
            x: course_groups[group]['group_exam_score'].map(item => item.points),
            type: 'histogram',
            name: group,  
            xbins: { size: 5 },
            xaxis: with_groups == false ? 'x1' : 'x2' ,
            yaxis: with_groups == false ? 'x1' : 'y2',
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
      height: 500,
      autosize: true,
      margin: { l: 40, r: 10, t: 80, b: 30 },
      xaxis: {
        dtick:10,
        tickfont: { size: 12, color: 'rgb(107, 107, 107)' },
        title: { text: with_groups == false ? 'Exam Scores' : '', font: { size: 12, color: '#7f7f7f' },  standoff: 10 },
        tickfont: { size: 12, color: 'rgb(107, 107, 107)' },
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

    if (with_groups){
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
                outliercolor: 'rgba(219, 64, 82, 0.6)',
                size: 5,
                line: { width: 1, color: '#333', outliercolor: 'rgba(219, 64, 82, 1.0)', outlierwidth: 2},
                opacity: 0.7
            },    
            };
        boxplot_traces.push(overall_boxplot_trace);        
    }

    const groups = Object.keys(course_groups);

    groups.forEach((group, idx) => {
        const groups_boxplot_hover_texts = course_groups[group]['group_exam_score'].map(item => [item.Name, item.Subject, item.topic]);
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
            xaxis: with_groups == false ? 'x1' : 'x2',
            yaxis: with_groups == false ? 'x1' : 'y2',
            marker: {
                size: 5,
                line: { width: 1, color: '#333', outliercolor: 'rgba(219, 64, 82, 1.0)', outlierwidth: 2},
                outliercolor: 'rgba(219, 64, 82, 0.6)',
                color: group_colors[idx % group_colors.length],  
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
        domain: [0, 1],
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

function renderSubjectHeatmap(){const subjects = [
  'Computer Programming 1',
  'Intro to Computing',
  'Computer Programming 2',
  'Data Structures',
  'Informatics Management',
  'Algorithms',
  'Databases',
  'Advanced Database',
  'Software Engineering 1',
  'Programming Language'
];

const studentGroups = ['BSCS', 'BSIT'];

const scores = [
  [0.20, 0.35, 0.10, 0.50, 0.38, 0.85, 0.78, 0.82, 0.74, 0.69], 
  [0.65, 0.60, 0.85, 0.78, 0.70, 0.72, 0.76, 0.71, 0.67, 0.63],  
];

// ✅ Transpose scores
const transposedScores = subjects.map((_, i) => studentGroups.map((_, j) => scores[j][i]));

// ✅ Transpose bloomDetails
const bloomDetails = [
  [ 
    { Remember: 80, Understand: 75, Apply: 70 }, 
    { Remember: 70, Understand: 65, Apply: 60 }, 
    { Remember: 90, Understand: 85, Apply: 80 }, 
    { Remember: 60, Understand: 55, Apply: 50 }
  ],
  [
    { Remember: 60, Understand: 55, Apply: 50 }, 
    { Remember: 55, Understand: 50, Apply: 45 }, 
    { Remember: 85, Understand: 80, Apply: 75 }, 
    { Remember: 78, Understand: 70, Apply: 68 } 
  ]
];

// Simulate full bloomDetails array to match subject count (only first 4 are defined in your data)
const extendedBloomDetails = bloomDetails.map(group =>
  [
    ...group,
    ...Array(subjects.length - group.length).fill({ Remember: '-', Understand: '-', Apply: '-' })
  ]
);

// ✅ Transpose extendedBloomDetails
const transposedBloom = subjects.map((_, i) =>
  studentGroups.map((_, j) => extendedBloomDetails[j][i])
);

// ✅ Generate hoverText
const hoverText = transposedBloom.map((row, i) =>
  row.map((bloom, j) => `
    <b>${studentGroups[j]}</b> - <b>${subjects[i]}</b><br>
    Normalized Score: ${Math.round(transposedScores[i][j] * 100)}%<br><br>
    <b>Bloom's Levels</b><br>
    🧠 Remember: ${bloom.Remember}%<br>
    🤔 Understand: ${bloom.Understand}%<br>
    ⚙️ Apply: ${bloom.Apply}%<br>
  `)
);

const customColorScale = [
  [0.0, 'rgb(255,255,255)'],
  [1.0, 'rgb(49,130,189)']
];

const data = [{
  z: transposedScores,
  x: studentGroups,
  y: subjects,
  type: 'heatmap',
  colorscale: customColorScale,
  zmin: 0,
  zmax: 1,
  xgap: 1,
  ygap: 1,
  hoverinfo: 'text',
  text: hoverText,
  colorbar: {
    title: 'Normalized Score',
    ticksuffix: ''
  }
}];

const layout = {
  title: {
    text: "Performance Distribution by Subjects",
    font: { size: 16, color: "#4B5563", family: "Arial Black, sans-serif" },
    x: 0.5,
    xanchor: 'center'
  },
  height: 500,
  autosize: true,
  margin: { l: 180, r: 20, t: 60, b: 80 },
  xaxis: { title: 'Student Groups' },
  yaxis: { title: 'Subjects', automargin: true },
};

Plotly.newPlot('exam-subjects', data, layout);
}

function renderTopicHeatmap(){
    const topics = [
  'Variables & Data Types',
  'Control Structures',
  'Functions & Procedures',
  'Arrays & Lists',
  'Object-Oriented Programming',
  'Recursion',
  'Sorting Algorithms',
  'Searching Algorithms',
  'Databases Basics',
  'Software Development Life Cycle'
];

const studentGroups = ['BSCS', 'BSIT'];

const scores = [
  [0.20, 0.35, 0.10, 0.50, 0.38, 0.85, 0.78, 0.82, 0.74, 0.69], 
  [0.65, 0.60, 0.85, 0.78, 0.70, 0.72, 0.76, 0.71, 0.67, 0.63],  
];

// ✅ Transpose scores to match vertical layout
const transposedScores = topics.map((_, i) => studentGroups.map((_, j) => scores[j][i]));

// Bloom's details per group → transpose too
const bloomDetails = [
  [ 
    { Remember: 80, Understand: 75, Apply: 70 }, 
    { Remember: 70, Understand: 65, Apply: 60 }, 
    { Remember: 90, Understand: 85, Apply: 80 }, 
    { Remember: 60, Understand: 55, Apply: 50 },
    { Remember: 75, Understand: 70, Apply: 68 },
    { Remember: 85, Understand: 80, Apply: 77 },
    { Remember: 79, Understand: 75, Apply: 70 },
    { Remember: 82, Understand: 80, Apply: 78 },
    { Remember: 74, Understand: 72, Apply: 69 },
    { Remember: 69, Understand: 65, Apply: 60 }
  ],
  [
    { Remember: 60, Understand: 55, Apply: 50 }, 
    { Remember: 55, Understand: 50, Apply: 45 }, 
    { Remember: 85, Understand: 80, Apply: 75 }, 
    { Remember: 78, Understand: 70, Apply: 68 },
    { Remember: 73, Understand: 68, Apply: 65 },
    { Remember: 75, Understand: 70, Apply: 67 },
    { Remember: 77, Understand: 73, Apply: 69 },
    { Remember: 71, Understand: 68, Apply: 65 },
    { Remember: 67, Understand: 65, Apply: 62 },
    { Remember: 63, Understand: 60, Apply: 58 }
  ],
];

// ✅ Transpose bloomDetails
const transposedBloom = topics.map((_, i) => studentGroups.map((_, j) => bloomDetails[j][i]));

const hoverText = transposedBloom.map((row, i) =>
  row.map((bloom, j) => `
    <b>${studentGroups[j]}</b> - <b>${topics[i]}</b><br>
    Normalized Score: ${Math.round(transposedScores[i][j] * 100)}%<br><br>
    <b>Bloom's Levels</b><br>
    🧠 Remember: ${bloom.Remember}%<br>
    🤔 Understand: ${bloom.Understand}%<br>
    ⚙️ Apply: ${bloom.Apply}%<br>
  `)
);

const customColorScale = [
  [0.0, 'rgb(255,255,255)'],    
  [1.0, 'rgb(87, 196, 133)']    
];

const data = [{
  z: transposedScores,
  x: studentGroups,      // flipped
  y: topics,             // flipped
  type: 'heatmap',
  colorscale: customColorScale,
  zmin: 0,
  zmax: 1,
  xgap: 1,
  ygap: 1,
  hoverinfo: 'text',
  text: hoverText,
  colorbar: {
    title: 'Normalized Score',
    ticksuffix: '%'
  }
}];

const layout = {
  title: {
    text: "Performance Distribution by Topics",
    font: { size: 16, color: "#4B5563", family: "Arial Black, sans-serif" },
    x: 0.5,
    xanchor: 'center'
  },
  height: 500,
  autosize: true,
  margin: { l: 180, r: 20, t: 60, b: 80 },
  xaxis: { title: 'Student Groups' },
  yaxis: { title: 'Topics', automargin: true },
};

Plotly.newPlot('exam-topics', data, layout);

}

function renderBarChartCompareTypesWithLevels(data){
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
function renderBarChartCompareTypesWithLevelsDistribution(data){
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
                y: normalized,
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
            text: "Distribution of Bloom levels within question types",
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

    Plotly.newPlot('exam-type-group-normalize', exam_compare_types_and_blooms_traces, exam_compare_types_and_blooms_layout).then(() => {
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
    margin: { l: 70, r: 20, t: 60, b: 100 },
    annotations: [
    {
        text: "Average Scores <br> in Percent",
        xref: "paper",
        yref: "paper",
        x: 1.07,       // align with colorbar (to the right of plot)
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
    renderSubjectHeatmap();
    renderTopicHeatmap();
    renderBarChartCompareTypesWithLevelsDistribution(exam_compare_types_and_blooms_data);
});