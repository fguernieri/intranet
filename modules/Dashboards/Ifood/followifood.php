<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/sidebar.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard iFood</title>
    <style>
        .btn-acao {
  min-width: 90px;
  height: 33px;
  padding: 0 12px;
  border-radius: 0.25rem;
  font-weight: bold;
  text-align: center;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background-color: #eab308; 
  color: #111827;            
  transition: background-color 0.2s;
}

.btn-acao:hover {
  background-color: #ca8a04;
}

.btn-acao-vermelho {
  min-width: 90px;
  height: 33px;
  padding: 0 12px;
  border-radius: 0.25rem;
  font-weight: bold;
  text-align: center;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background-color: #dc2626; /* bg-red-600 */
  color: #ffffff;            /* white */
  transition: background-color 0.2s;
}

.btn-acao-vermelho:hover {
  background-color: #b91c1c; /* bg-red-700 */
}

.divider_yellow {
  margin-top: 1.5rem;
  margin-bottom: 1.5rem;
  border: none;
  border-top: 1px solid #eab308; /* cor semelhante ao yellow-500 do Tailwind */
}

.card1 {
  background-color: rgba(255, 255, 255, 0.05);
  border-radius: 0.75rem; /* rounded-xl */
  padding: 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.1s ease-in-out;
}
.card1:hover {
  transform: scale(1.1);
}
.card1 p:first-child {
  font-size: 0.875rem; /* text-sm */
  color: #d1d5db;      /* gray-300 */
}
.card1 p:last-child {
  font-size: 1.5rem;   /* text-2xl */
  font-weight: bold;
}

card1.no-hover:hover {
  transform: none !important;
}

main {
  overflow-y: auto;
}


/* 1. Container do switch */
.custom-switch {
  position: relative;
  display: inline-flex;
  align-items: center;
  cursor: pointer;
}

/* 2. Checkbox “invisível” (sr-only) */
.custom-switch-input {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

/* 3. Track do switch */
.custom-switch-slider {
  width: 44px;              /* w-11 */
  height: 24px;             /* h-6 */
  background-color: #e5e7eb;/* bg-gray-200 */
  border-radius: 9999px;    /* rounded-full */
  position: relative;
  transition: background-color 0.2s;
}

/* 4. Thumb (a “bolinha”) */
.custom-switch-slider::after {
  content: "";
  position: absolute;
  top: 2px;
  left: 2px;
  width: 20px;              /* h-5/w-5 */
  height: 20px;
  background-color: #ffffff;/* bg-white */
  border-radius: 9999px;    /* rounded-full */
  transition: transform 0.2s;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* 5. Estado ligado (checked) */
.custom-switch-input:checked + .custom-switch-slider {
  background-color: #2563eb;/* bg-blue-600 */
}
.custom-switch-input:checked + .custom-switch-slider::after {
  transform: translateX(20px);
}

/* 6. Foco (análogo ao ring) */
.custom-switch-input:focus + .custom-switch-slider {
  box-shadow: 0 0 0 4px rgba(147, 197, 253, 0.5); /* ring-blue-300 */
}

/* 7. Label de texto ao lado */
.custom-switch-label {
  margin-left: 0.75rem;     /* ml-3 */
  font-size: 0.875rem;      /* text-sm */
  font-weight: 500;         /* font-medium */
  color: #e5e7eb;           /* text-gray-200 */
}

/* 8. Modo escuro (prefers-color-scheme) */

@media (prefers-color-scheme: dark) {
  .custom-switch-slider {
    background-color: #374151;/* bg-gray-700 */
  }
  .custom-switch-input:checked + .custom-switch-slider {
    background-color: #2563eb;/* manter blue-600 */
  }
}

    .bk-root {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .bk-root .bk-plot {
        border-radius: 10px;
    }
    
    .grafico-container {
        margin: 20px auto;
        padding: 15px;
        background: #f8f8f8;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        overflow: hidden;  /* This ensures inner elements respect the border radius */
    }
    
    .grafico-container {
        margin: 15px auto;
        padding: 15px;
    }
    
    .grafico-container {
        margin: 20px auto;
        padding: 15px;
    }
    
    .bk-root {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .bk-root .bk-plot {
        border-radius: 10px;
    }
    
    .grafico-container {
        margin: 20px auto;
        padding: 15px;
        background: #f8f8f8;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        overflow: hidden;  /* This ensures inner elements respect the border radius */
    }
    
    .grafico-container {
        margin: 15px auto;
        padding: 15px;
    }
    
    .grafico-container {
        margin: 20px auto;
        padding: 15px;
    }
    
    .bk-root {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .bk-root .bk-plot {
        border-radius: 10px;
    }
    
    .grafico-container {
        margin: 20px auto;
        padding: 15px;
        background: #f8f8f8;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        overflow: hidden;  /* This ensures inner elements respect the border radius */
    }
    
    .grafico-container {
        margin: 15px auto;
        padding: 15px;
    }
    
    .grafico-container {
        margin: 20px auto;
        padding: 15px;
    }
    
    .bk-root {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .bk-root .bk-plot {
        border-radius: 10px;
    }
    
    .grafico-container {
        margin: 20px auto;
        padding: 15px;
        background: #f8f8f8;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        overflow: hidden;  /* This ensures inner elements respect the border radius */
    }
    
    .grafico-container {
        margin: 15px auto;
        padding: 15px;
    }
    
    .grafico-container {
        margin: 20px auto;
        padding: 15px;
    }
    
.grafico-container {
    padding-bottom: 40px !important;  /* Extra padding for legend */
}

/* Base table styles */
#matriz-ifood {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin: 20px 0;
    color: #000000;
    background: #ffffff;
    font-size: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

#matriz-ifood th {
    background-color: #f1f1f1;
    padding: 8px;
    text-align: left;
    font-weight: bold;
    border-bottom: 2px solid #ddd;
    border-right: 1px solid #ddd;
    font-size: 12px;
}

#matriz-ifood td {
    padding: 6px;
    border-bottom: 1px solid #ddd;
    border-right: 1px solid #ddd;
    font-size: 12px;
}

/* Remove right border from last column */
#matriz-ifood th:last-child,
#matriz-ifood td:last-child {
    border-right: none;
}

/* Different shades of gray for column pairs */
/* Desconto */
#matriz-ifood td:nth-child(4),
#matriz-ifood td:nth-child(5),
#matriz-ifood th:nth-child(4),
#matriz-ifood th:nth-child(5) {
    background-color: #e0e0e0;
}

/* Entrega */
#matriz-ifood td:nth-child(6),
#matriz-ifood td:nth-child(7),
#matriz-ifood th:nth-child(6),
#matriz-ifood th:nth-child(7) {
    background-color: #e8e8e8;
}

/* Comissão */
#matriz-ifood td:nth-child(8),
#matriz-ifood td:nth-child(9),
#matriz-ifood th:nth-child(8),
#matriz-ifood th:nth-child(9) {
    background-color: #f0f0f0;
}

/* Total Descontos - without bold */
#matriz-ifood td:nth-child(10),
#matriz-ifood td:nth-child(11),
#matriz-ifood th:nth-child(10),
#matriz-ifood th:nth-child(11) {
    background-color: #f8f8f8;
    font-weight: normal;
}

/* Faturamento Líquido - green background and bold */
#matriz-ifood td:nth-child(12),
#matriz-ifood td:nth-child(13),
#matriz-ifood th:nth-child(12),
#matriz-ifood th:nth-child(13) {
    background-color: #e6ffe6;  /* Light green */
    font-weight: bold;
}

/* Remove spacing between value and percentage pairs */
#matriz-ifood td:nth-child(4),
#matriz-ifood td:nth-child(6),
#matriz-ifood td:nth-child(8),
#matriz-ifood td:nth-child(10),
#matriz-ifood td:nth-child(12) {
    padding-right: 0;
    border-right: none;
}

#matriz-ifood td:nth-child(5),
#matriz-ifood td:nth-child(7),
#matriz-ifood td:nth-child(9),
#matriz-ifood td:nth-child(11),
#matriz-ifood td:nth-child(13) {
    padding-left: 0;
}

/* Add vertical separation between groups */
#matriz-ifood td:nth-child(5),
#matriz-ifood td:nth-child(7),
#matriz-ifood td:nth-child(9),
#matriz-ifood td:nth-child(11) {
    border-right: 2px solid #fff;
}

.matriz-container {
    margin: 30px auto;
    padding: 20px;
    background: #f8f8f8;  /* Mudado para cinza claro */
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

#matriz-ifood {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    color: #000000;
    background: #ffffff;  /* Fundo branco para tabela */
}

#matriz-ifood th {
    background-color: #f1f1f1;
    padding: 12px;
    text-align: left;
    font-weight: bold;
    border-bottom: 2px solid #ddd;
}

#matriz-ifood td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    color: #000000;
}

/* Estilo para as colunas de desconto - incluindo background */
#matriz-ifood td:nth-child(4),
#matriz-ifood td:nth-child(5),
#matriz-ifood th:nth-child(4),
#matriz-ifood th:nth-child(5) {
    background-color: #FFF5F5;  /* Fundo levemente rosado */
    color: #000000;  /* Texto preto */
    padding-right: 2px;
    padding-left: 2px;
}

#matriz-ifood tr:hover td {
    background-color: #f5f5f5;
}

/* Mantém o background das colunas de desconto mesmo no hover */
#matriz-ifood tr:hover td:nth-child(4),
#matriz-ifood tr:hover td:nth-child(5) {
    background-color: #FFF5F5;
}

        .grafico-container {
            margin: 20px auto;
            padding: 15px;
            background: #f8f8f8;  /* Light gray background */
            border: 1px solid #ddd;
            border-radius: 10px;  /* Increased from 5px to 10px */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .grafico-title {
            font-size: 14pt;
            font-weight: bold;  /* Added bold */
            color: #333;
            margin: 0 0 15px 0;
            padding: 0;
            text-align: center;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-row">
    <!-- Conteúdo Principal -->
    <main class="flex-1 p-6">
        <h1 class="text-2xl font-bold text-yellow-400 mb-4">Dashboard iFood</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class='grafico-container'>
                <h2 class="grafico-title">Desconto custeado (R$) (mensal)</h2>
                <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Gráfico - Desconto custeado (R$)</title>
    <style>
      html, body {
        box-sizing: border-box;
        display: flow-root;
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
    <script type="text/javascript" src="https://cdn.bokeh.org/bokeh/release/bokeh-3.7.2.min.js"></script>
    <script type="text/javascript">
        Bokeh.set_log_level("info");
    </script>
  </head>
  <body>
    <div id="fcc1bfd3-3f55-4217-8273-723ce21ee4a4" data-root-id="p1004" style="display: contents;"></div>
  
    <script type="application/json" id="ebd44caf-f92b-4267-861b-5686e931c363">
      {"9a203cfd-36cd-4b1b-ba2b-f2ae91af4e71":{"version":"3.7.2","title":"Bokeh Application","roots":[{"type":"object","name":"Figure","id":"p1004","attributes":{"name":"graph_vlr_desconto_loja","height":300,"margin":[10,10,10,10],"x_range":{"type":"object","name":"DataRange1d","id":"p1005"},"y_range":{"type":"object","name":"DataRange1d","id":"p1006"},"x_scale":{"type":"object","name":"LinearScale","id":"p1013"},"y_scale":{"type":"object","name":"LinearScale","id":"p1014"},"extra_y_ranges":{"type":"map","entries":[["pedidos",{"type":"object","name":"Range1d","id":"p1059","attributes":{"start":147.6,"end":938.3000000000001}}]]},"title":{"type":"object","name":"Title","id":"p1011"},"renderers":[{"type":"object","name":"GlyphRenderer","id":"p1045","attributes":{"data_source":{"type":"object","name":"ColumnDataSource","id":"p1001","attributes":{"selected":{"type":"object","name":"Selection","id":"p1002","attributes":{"indices":[],"line_indices":[]}},"selection_policy":{"type":"object","name":"UnionRenderers","id":"p1003"},"data":{"type":"map","entries":[["x",{"type":"ndarray","array":{"type":"bytes","data":"AADABlYkeUIAAABZUC55QgAAgEX4N3lCAADAl/JBeUIAAADq7Et5QgAAAAvwVHlCAABAXepeeUI="},"shape":[7],"dtype":"float64","order":"little"}],["y",{"type":"ndarray","array":{"type":"bytes","data":"AAAAAAAAAAAZ4XoUzqbDQGmZmZmZ5MVAO7gehdvE0EDMhOtRuOHNQBRmZmZmPs5AOo/C9Rg80EA="},"shape":[7],"dtype":"float64","order":"little"}],["valor",["R$ 0.00","R$ 10061.61","R$ 11209.20","R$ 17171.43","R$ 15299.44","R$ 15484.80","R$ 16624.39"]],["pedidos",{"type":"ndarray","array":{"type":"bytes","data":"pAAAADwCAAA7AgAASgMAAEYDAABVAwAATAMAAA=="},"shape":[7],"dtype":"int32","order":"little"}]]}}},"view":{"type":"object","name":"CDSView","id":"p1046","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1047"}}},"glyph":{"type":"object","name":"Line","id":"p1042","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"line_color":"#7FB069","line_width":2}},"nonselection_glyph":{"type":"object","name":"Line","id":"p1043","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"line_color":"#7FB069","line_alpha":0.1,"line_width":2}},"muted_glyph":{"type":"object","name":"Line","id":"p1044","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"line_color":"#7FB069","line_alpha":0.2,"line_width":2}}}},{"type":"object","name":"GlyphRenderer","id":"p1056","attributes":{"data_source":{"id":"p1001"},"view":{"type":"object","name":"CDSView","id":"p1057","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1058"}}},"glyph":{"type":"object","name":"Scatter","id":"p1053","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"size":{"type":"value","value":6},"line_color":{"type":"value","value":"#7FB069"},"fill_color":{"type":"value","value":"#7FB069"},"hatch_color":{"type":"value","value":"#7FB069"}}},"nonselection_glyph":{"type":"object","name":"Scatter","id":"p1054","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"size":{"type":"value","value":6},"line_color":{"type":"value","value":"#7FB069"},"line_alpha":{"type":"value","value":0.1},"fill_color":{"type":"value","value":"#7FB069"},"fill_alpha":{"type":"value","value":0.1},"hatch_color":{"type":"value","value":"#7FB069"},"hatch_alpha":{"type":"value","value":0.1}}},"muted_glyph":{"type":"object","name":"Scatter","id":"p1055","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"size":{"type":"value","value":6},"line_color":{"type":"value","value":"#7FB069"},"line_alpha":{"type":"value","value":0.2},"fill_color":{"type":"value","value":"#7FB069"},"fill_alpha":{"type":"value","value":0.2},"hatch_color":{"type":"value","value":"#7FB069"},"hatch_alpha":{"type":"value","value":0.2}}}}},{"type":"object","name":"GlyphRenderer","id":"p1070","attributes":{"y_range_name":"pedidos","data_source":{"id":"p1001"},"view":{"type":"object","name":"CDSView","id":"p1071","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1072"}}},"glyph":{"type":"object","name":"Line","id":"p1067","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":"gray","line_width":2}},"nonselection_glyph":{"type":"object","name":"Line","id":"p1068","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":"gray","line_alpha":0.1,"line_width":2}},"muted_glyph":{"type":"object","name":"Line","id":"p1069","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":"gray","line_alpha":0.2,"line_width":2}}}},{"type":"object","name":"GlyphRenderer","id":"p1080","attributes":{"y_range_name":"pedidos","data_source":{"id":"p1001"},"view":{"type":"object","name":"CDSView","id":"p1081","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1082"}}},"glyph":{"type":"object","name":"Scatter","id":"p1077","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":{"type":"value","value":"gray"},"hatch_color":{"type":"value","value":"gray"}}},"nonselection_glyph":{"type":"object","name":"Scatter","id":"p1078","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":{"type":"value","value":"gray"},"line_alpha":{"type":"value","value":0.1},"fill_alpha":{"type":"value","value":0.1},"hatch_color":{"type":"value","value":"gray"},"hatch_alpha":{"type":"value","value":0.1}}},"muted_glyph":{"type":"object","name":"Scatter","id":"p1079","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":{"type":"value","value":"gray"},"line_alpha":{"type":"value","value":0.2},"fill_alpha":{"type":"value","value":0.2},"hatch_color":{"type":"value","value":"gray"},"hatch_alpha":{"type":"value","value":0.2}}}}}],"toolbar":{"type":"object","name":"Toolbar","id":"p1012","attributes":{"tools":[{"type":"object","name":"HoverTool","id":"p1083","attributes":{"renderers":[{"id":"p1045"},{"id":"p1056"}],"tooltips":[["M\u00eas/Ano","@x{%b/%Y}"],["Pedidos","@pedidos"],["Desconto custeado (R$)","@valor"]],"formatters":{"type":"map","entries":[["@x","datetime"]]},"mode":"vline"}}]}},"toolbar_location":null,"left":[{"type":"object","name":"LinearAxis","id":"p1034","attributes":{"ticker":{"type":"object","name":"BasicTicker","id":"p1035","attributes":{"mantissas":[1,2,5]}},"formatter":{"type":"object","name":"BasicTickFormatter","id":"p1036"},"major_label_policy":{"type":"object","name":"AllLabels","id":"p1037"},"major_label_text_color":"#666666","major_label_text_font_size":"10pt","axis_line_color":null,"major_tick_line_color":null,"minor_tick_line_color":null}}],"right":[{"type":"object","name":"LinearAxis","id":"p1060","attributes":{"y_range_name":"pedidos","ticker":{"type":"object","name":"BasicTicker","id":"p1061","attributes":{"mantissas":[1,2,5]}},"formatter":{"type":"object","name":"BasicTickFormatter","id":"p1062"},"major_label_policy":{"type":"object","name":"AllLabels","id":"p1063"},"major_label_text_color":"#666666","major_label_text_font_size":"10pt","axis_line_color":null,"major_tick_line_color":null,"minor_tick_line_color":null}}],"below":[{"type":"object","name":"DatetimeAxis","id":"p1015","attributes":{"ticker":{"type":"object","name":"DatetimeTicker","id":"p1016","attributes":{"num_minor_ticks":5,"tickers":[{"type":"object","name":"AdaptiveTicker","id":"p1017","attributes":{"num_minor_ticks":0,"mantissas":[1,2,5],"max_interval":500.0}},{"type":"object","name":"AdaptiveTicker","id":"p1018","attributes":{"num_minor_ticks":0,"base":60,"mantissas":[1,2,5,10,15,20,30],"min_interval":1000.0,"max_interval":1800000.0}},{"type":"object","name":"AdaptiveTicker","id":"p1019","attributes":{"num_minor_ticks":0,"base":24,"mantissas":[1,2,4,6,8,12],"min_interval":3600000.0,"max_interval":43200000.0}},{"type":"object","name":"DaysTicker","id":"p1020","attributes":{"days":[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31]}},{"type":"object","name":"DaysTicker","id":"p1021","attributes":{"days":[1,4,7,10,13,16,19,22,25,28]}},{"type":"object","name":"DaysTicker","id":"p1022","attributes":{"days":[1,8,15,22]}},{"type":"object","name":"DaysTicker","id":"p1023","attributes":{"days":[1,15]}},{"type":"object","name":"MonthsTicker","id":"p1024","attributes":{"months":[0,1,2,3,4,5,6,7,8,9,10,11]}},{"type":"object","name":"MonthsTicker","id":"p1025","attributes":{"months":[0,2,4,6,8,10]}},{"type":"object","name":"MonthsTicker","id":"p1026","attributes":{"months":[0,4,8]}},{"type":"object","name":"MonthsTicker","id":"p1027","attributes":{"months":[0,6]}},{"type":"object","name":"YearsTicker","id":"p1028"}]}},"formatter":{"type":"object","name":"DatetimeTickFormatter","id":"p1031","attributes":{"seconds":"%T","minsec":"%T","minutes":"%H:%M","hours":"%H:%M","days":"%b %d","months":"%b %Y","strip_leading_zeros":["microseconds","milliseconds","seconds"],"boundary_scaling":false,"context":{"type":"object","name":"DatetimeTickFormatter","id":"p1030","attributes":{"microseconds":"%T","milliseconds":"%T","seconds":"%b %d, %Y","minsec":"%b %d, %Y","minutes":"%b %d, %Y","hourmin":"%b %d, %Y","hours":"%b %d, %Y","days":"%Y","months":"","years":"","boundary_scaling":false,"hide_repeats":true,"context":{"type":"object","name":"DatetimeTickFormatter","id":"p1029","attributes":{"microseconds":"%b %d, %Y","milliseconds":"%b %d, %Y","seconds":"","minsec":"","minutes":"","hourmin":"","hours":"","days":"","months":"","years":"","boundary_scaling":false,"hide_repeats":true}},"context_which":"all"}},"context_which":"all"}},"major_label_policy":{"type":"object","name":"AllLabels","id":"p1032"},"major_label_text_color":"#666666","major_label_text_font_size":"10pt","axis_line_color":null,"major_tick_line_color":null,"minor_tick_line_color":null}}],"center":[{"type":"object","name":"Grid","id":"p1033","attributes":{"axis":{"id":"p1015"},"grid_line_color":null}},{"type":"object","name":"Grid","id":"p1038","attributes":{"dimension":1,"axis":{"id":"p1034"},"grid_line_color":null}},{"type":"object","name":"Legend","id":"p1048","attributes":{"location":"bottom_center","orientation":"horizontal","border_line_color":null,"background_fill_color":null,"click_policy":"hide","label_text_font_size":"10pt","margin":0,"padding":5,"spacing":20,"items":[{"type":"object","name":"LegendItem","id":"p1049","attributes":{"label":{"type":"value","value":"Desconto custeado (R$)"},"renderers":[{"id":"p1045"},{"id":"p1056"}]}},{"type":"object","name":"LegendItem","id":"p1073","attributes":{"label":{"type":"value","value":"Contagem Pedidos"},"renderers":[{"id":"p1070"},{"id":"p1080"}]}}]}}]}}]}}
    </script>
    <script type="text/javascript">
      (function() {
        const fn = function() {
          Bokeh.safely(function() {
            (function(root) {
              function embed_document(root) {
              const docs_json = document.getElementById('ebd44caf-f92b-4267-861b-5686e931c363').textContent;
              const render_items = [{"docid":"9a203cfd-36cd-4b1b-ba2b-f2ae91af4e71","roots":{"p1004":"fcc1bfd3-3f55-4217-8273-723ce21ee4a4"},"root_ids":["p1004"]}];
              root.Bokeh.embed.embed_items(docs_json, render_items);
              }
              if (root.Bokeh !== undefined) {
                embed_document(root);
              } else {
                let attempts = 0;
                const timer = setInterval(function(root) {
                  if (root.Bokeh !== undefined) {
                    clearInterval(timer);
                    embed_document(root);
                  } else {
                    attempts++;
                    if (attempts > 100) {
                      clearInterval(timer);
                      console.log("Bokeh: ERROR: Unable to run BokehJS code because BokehJS library is missing");
                    }
                  }
                }, 10, root)
              }
            })(window);
          });
        };
        if (document.readyState != "loading") fn();
        else document.addEventListener("DOMContentLoaded", fn);
      })();
    </script>
  </body>
</html>
            </div><div class='grafico-container'>
                <h2 class="grafico-title">Entrega custeada (R$) (mensal)</h2>
                <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Gráfico - Entrega custeada (R$)</title>
    <style>
      html, body {
        box-sizing: border-box;
        display: flow-root;
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
    <script type="text/javascript" src="https://cdn.bokeh.org/bokeh/release/bokeh-3.7.2.min.js"></script>
    <script type="text/javascript">
        Bokeh.set_log_level("info");
    </script>
  </head>
  <body>
    <div id="e77e20e5-740d-4afe-9cd6-2d426e9159ef" data-root-id="p1090" style="display: contents;"></div>
  
    <script type="application/json" id="b0f39e27-475f-4bbc-8508-b7215be17c49">
      {"ee477f0d-9473-437f-acc2-0eb5a123a780":{"version":"3.7.2","title":"Bokeh Application","roots":[{"type":"object","name":"Figure","id":"p1090","attributes":{"name":"graph_vlr_entrega_ifood","height":300,"margin":[10,10,10,10],"x_range":{"type":"object","name":"DataRange1d","id":"p1091"},"y_range":{"type":"object","name":"DataRange1d","id":"p1092"},"x_scale":{"type":"object","name":"LinearScale","id":"p1099"},"y_scale":{"type":"object","name":"LinearScale","id":"p1100"},"extra_y_ranges":{"type":"map","entries":[["pedidos",{"type":"object","name":"Range1d","id":"p1145","attributes":{"start":147.6,"end":938.3000000000001}}]]},"title":{"type":"object","name":"Title","id":"p1097"},"renderers":[{"type":"object","name":"GlyphRenderer","id":"p1131","attributes":{"data_source":{"type":"object","name":"ColumnDataSource","id":"p1087","attributes":{"selected":{"type":"object","name":"Selection","id":"p1088","attributes":{"indices":[],"line_indices":[]}},"selection_policy":{"type":"object","name":"UnionRenderers","id":"p1089"},"data":{"type":"map","entries":[["x",{"type":"ndarray","array":{"type":"bytes","data":"AADABlYkeUIAAABZUC55QgAAgEX4N3lCAADAl/JBeUIAAADq7Et5QgAAAAvwVHlCAABAXepeeUI="},"shape":[7],"dtype":"float64","order":"little"}],["y",{"type":"ndarray","array":{"type":"bytes","data":"QwrXo3A1mkBmHoXrUZa2QGn1KFxPZbhAiaNwPUqUwUAWR+F6lNe+QDPMzMyMhr9AEo/C9UiywEA="},"shape":[7],"dtype":"float64","order":"little"}],["valor",["R$ 1677.36","R$ 5782.32","R$ 6245.31","R$ 9000.58","R$ 7895.58","R$ 8070.55","R$ 8548.57"]],["pedidos",{"type":"ndarray","array":{"type":"bytes","data":"pAAAADwCAAA7AgAASgMAAEYDAABVAwAATAMAAA=="},"shape":[7],"dtype":"int32","order":"little"}]]}}},"view":{"type":"object","name":"CDSView","id":"p1132","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1133"}}},"glyph":{"type":"object","name":"Line","id":"p1128","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"line_color":"#4A90E2","line_width":2}},"nonselection_glyph":{"type":"object","name":"Line","id":"p1129","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"line_color":"#4A90E2","line_alpha":0.1,"line_width":2}},"muted_glyph":{"type":"object","name":"Line","id":"p1130","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"line_color":"#4A90E2","line_alpha":0.2,"line_width":2}}}},{"type":"object","name":"GlyphRenderer","id":"p1142","attributes":{"data_source":{"id":"p1087"},"view":{"type":"object","name":"CDSView","id":"p1143","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1144"}}},"glyph":{"type":"object","name":"Scatter","id":"p1139","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"size":{"type":"value","value":6},"line_color":{"type":"value","value":"#4A90E2"},"fill_color":{"type":"value","value":"#4A90E2"},"hatch_color":{"type":"value","value":"#4A90E2"}}},"nonselection_glyph":{"type":"object","name":"Scatter","id":"p1140","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"size":{"type":"value","value":6},"line_color":{"type":"value","value":"#4A90E2"},"line_alpha":{"type":"value","value":0.1},"fill_color":{"type":"value","value":"#4A90E2"},"fill_alpha":{"type":"value","value":0.1},"hatch_color":{"type":"value","value":"#4A90E2"},"hatch_alpha":{"type":"value","value":0.1}}},"muted_glyph":{"type":"object","name":"Scatter","id":"p1141","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"size":{"type":"value","value":6},"line_color":{"type":"value","value":"#4A90E2"},"line_alpha":{"type":"value","value":0.2},"fill_color":{"type":"value","value":"#4A90E2"},"fill_alpha":{"type":"value","value":0.2},"hatch_color":{"type":"value","value":"#4A90E2"},"hatch_alpha":{"type":"value","value":0.2}}}}},{"type":"object","name":"GlyphRenderer","id":"p1156","attributes":{"y_range_name":"pedidos","data_source":{"id":"p1087"},"view":{"type":"object","name":"CDSView","id":"p1157","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1158"}}},"glyph":{"type":"object","name":"Line","id":"p1153","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":"gray","line_width":2}},"nonselection_glyph":{"type":"object","name":"Line","id":"p1154","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":"gray","line_alpha":0.1,"line_width":2}},"muted_glyph":{"type":"object","name":"Line","id":"p1155","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":"gray","line_alpha":0.2,"line_width":2}}}},{"type":"object","name":"GlyphRenderer","id":"p1166","attributes":{"y_range_name":"pedidos","data_source":{"id":"p1087"},"view":{"type":"object","name":"CDSView","id":"p1167","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1168"}}},"glyph":{"type":"object","name":"Scatter","id":"p1163","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":{"type":"value","value":"gray"},"hatch_color":{"type":"value","value":"gray"}}},"nonselection_glyph":{"type":"object","name":"Scatter","id":"p1164","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":{"type":"value","value":"gray"},"line_alpha":{"type":"value","value":0.1},"fill_alpha":{"type":"value","value":0.1},"hatch_color":{"type":"value","value":"gray"},"hatch_alpha":{"type":"value","value":0.1}}},"muted_glyph":{"type":"object","name":"Scatter","id":"p1165","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":{"type":"value","value":"gray"},"line_alpha":{"type":"value","value":0.2},"fill_alpha":{"type":"value","value":0.2},"hatch_color":{"type":"value","value":"gray"},"hatch_alpha":{"type":"value","value":0.2}}}}}],"toolbar":{"type":"object","name":"Toolbar","id":"p1098","attributes":{"tools":[{"type":"object","name":"HoverTool","id":"p1169","attributes":{"renderers":[{"id":"p1131"},{"id":"p1142"}],"tooltips":[["M\u00eas/Ano","@x{%b/%Y}"],["Pedidos","@pedidos"],["Entrega custeada (R$)","@valor"]],"formatters":{"type":"map","entries":[["@x","datetime"]]},"mode":"vline"}}]}},"toolbar_location":null,"left":[{"type":"object","name":"LinearAxis","id":"p1120","attributes":{"ticker":{"type":"object","name":"BasicTicker","id":"p1121","attributes":{"mantissas":[1,2,5]}},"formatter":{"type":"object","name":"BasicTickFormatter","id":"p1122"},"major_label_policy":{"type":"object","name":"AllLabels","id":"p1123"},"major_label_text_color":"#666666","major_label_text_font_size":"10pt","axis_line_color":null,"major_tick_line_color":null,"minor_tick_line_color":null}}],"right":[{"type":"object","name":"LinearAxis","id":"p1146","attributes":{"y_range_name":"pedidos","ticker":{"type":"object","name":"BasicTicker","id":"p1147","attributes":{"mantissas":[1,2,5]}},"formatter":{"type":"object","name":"BasicTickFormatter","id":"p1148"},"major_label_policy":{"type":"object","name":"AllLabels","id":"p1149"},"major_label_text_color":"#666666","major_label_text_font_size":"10pt","axis_line_color":null,"major_tick_line_color":null,"minor_tick_line_color":null}}],"below":[{"type":"object","name":"DatetimeAxis","id":"p1101","attributes":{"ticker":{"type":"object","name":"DatetimeTicker","id":"p1102","attributes":{"num_minor_ticks":5,"tickers":[{"type":"object","name":"AdaptiveTicker","id":"p1103","attributes":{"num_minor_ticks":0,"mantissas":[1,2,5],"max_interval":500.0}},{"type":"object","name":"AdaptiveTicker","id":"p1104","attributes":{"num_minor_ticks":0,"base":60,"mantissas":[1,2,5,10,15,20,30],"min_interval":1000.0,"max_interval":1800000.0}},{"type":"object","name":"AdaptiveTicker","id":"p1105","attributes":{"num_minor_ticks":0,"base":24,"mantissas":[1,2,4,6,8,12],"min_interval":3600000.0,"max_interval":43200000.0}},{"type":"object","name":"DaysTicker","id":"p1106","attributes":{"days":[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31]}},{"type":"object","name":"DaysTicker","id":"p1107","attributes":{"days":[1,4,7,10,13,16,19,22,25,28]}},{"type":"object","name":"DaysTicker","id":"p1108","attributes":{"days":[1,8,15,22]}},{"type":"object","name":"DaysTicker","id":"p1109","attributes":{"days":[1,15]}},{"type":"object","name":"MonthsTicker","id":"p1110","attributes":{"months":[0,1,2,3,4,5,6,7,8,9,10,11]}},{"type":"object","name":"MonthsTicker","id":"p1111","attributes":{"months":[0,2,4,6,8,10]}},{"type":"object","name":"MonthsTicker","id":"p1112","attributes":{"months":[0,4,8]}},{"type":"object","name":"MonthsTicker","id":"p1113","attributes":{"months":[0,6]}},{"type":"object","name":"YearsTicker","id":"p1114"}]}},"formatter":{"type":"object","name":"DatetimeTickFormatter","id":"p1117","attributes":{"seconds":"%T","minsec":"%T","minutes":"%H:%M","hours":"%H:%M","days":"%b %d","months":"%b %Y","strip_leading_zeros":["microseconds","milliseconds","seconds"],"boundary_scaling":false,"context":{"type":"object","name":"DatetimeTickFormatter","id":"p1116","attributes":{"microseconds":"%T","milliseconds":"%T","seconds":"%b %d, %Y","minsec":"%b %d, %Y","minutes":"%b %d, %Y","hourmin":"%b %d, %Y","hours":"%b %d, %Y","days":"%Y","months":"","years":"","boundary_scaling":false,"hide_repeats":true,"context":{"type":"object","name":"DatetimeTickFormatter","id":"p1115","attributes":{"microseconds":"%b %d, %Y","milliseconds":"%b %d, %Y","seconds":"","minsec":"","minutes":"","hourmin":"","hours":"","days":"","months":"","years":"","boundary_scaling":false,"hide_repeats":true}},"context_which":"all"}},"context_which":"all"}},"major_label_policy":{"type":"object","name":"AllLabels","id":"p1118"},"major_label_text_color":"#666666","major_label_text_font_size":"10pt","axis_line_color":null,"major_tick_line_color":null,"minor_tick_line_color":null}}],"center":[{"type":"object","name":"Grid","id":"p1119","attributes":{"axis":{"id":"p1101"},"grid_line_color":null}},{"type":"object","name":"Grid","id":"p1124","attributes":{"dimension":1,"axis":{"id":"p1120"},"grid_line_color":null}},{"type":"object","name":"Legend","id":"p1134","attributes":{"location":"bottom_center","orientation":"horizontal","border_line_color":null,"background_fill_color":null,"click_policy":"hide","label_text_font_size":"10pt","margin":0,"padding":5,"spacing":20,"items":[{"type":"object","name":"LegendItem","id":"p1135","attributes":{"label":{"type":"value","value":"Entrega custeada (R$)"},"renderers":[{"id":"p1131"},{"id":"p1142"}]}},{"type":"object","name":"LegendItem","id":"p1159","attributes":{"label":{"type":"value","value":"Contagem Pedidos"},"renderers":[{"id":"p1156"},{"id":"p1166"}]}}]}}]}}]}}
    </script>
    <script type="text/javascript">
      (function() {
        const fn = function() {
          Bokeh.safely(function() {
            (function(root) {
              function embed_document(root) {
              const docs_json = document.getElementById('b0f39e27-475f-4bbc-8508-b7215be17c49').textContent;
              const render_items = [{"docid":"ee477f0d-9473-437f-acc2-0eb5a123a780","roots":{"p1090":"e77e20e5-740d-4afe-9cd6-2d426e9159ef"},"root_ids":["p1090"]}];
              root.Bokeh.embed.embed_items(docs_json, render_items);
              }
              if (root.Bokeh !== undefined) {
                embed_document(root);
              } else {
                let attempts = 0;
                const timer = setInterval(function(root) {
                  if (root.Bokeh !== undefined) {
                    clearInterval(timer);
                    embed_document(root);
                  } else {
                    attempts++;
                    if (attempts > 100) {
                      clearInterval(timer);
                      console.log("Bokeh: ERROR: Unable to run BokehJS code because BokehJS library is missing");
                    }
                  }
                }, 10, root)
              }
            })(window);
          });
        };
        if (document.readyState != "loading") fn();
        else document.addEventListener("DOMContentLoaded", fn);
      })();
    </script>
  </body>
</html>
            </div><div class='grafico-container'>
                <h2 class="grafico-title">Comissão iFood (R$) (mensal)</h2>
                <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Gráfico - Comissão iFood (R$)</title>
    <style>
      html, body {
        box-sizing: border-box;
        display: flow-root;
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
    <script type="text/javascript" src="https://cdn.bokeh.org/bokeh/release/bokeh-3.7.2.min.js"></script>
    <script type="text/javascript">
        Bokeh.set_log_level("info");
    </script>
  </head>
  <body>
    <div id="decc531c-8417-4324-8796-62a441a74fb7" data-root-id="p1176" style="display: contents;"></div>
  
    <script type="application/json" id="ade63b60-612c-46fa-b352-f8b63986ccde">
      {"ceca7ddb-5efd-485d-bd46-c5ff8b230b9e":{"version":"3.7.2","title":"Bokeh Application","roots":[{"type":"object","name":"Figure","id":"p1176","attributes":{"name":"graph_vlr_comissao_ifood","height":300,"margin":[10,10,10,10],"x_range":{"type":"object","name":"DataRange1d","id":"p1177"},"y_range":{"type":"object","name":"DataRange1d","id":"p1178"},"x_scale":{"type":"object","name":"LinearScale","id":"p1185"},"y_scale":{"type":"object","name":"LinearScale","id":"p1186"},"extra_y_ranges":{"type":"map","entries":[["pedidos",{"type":"object","name":"Range1d","id":"p1231","attributes":{"start":147.6,"end":938.3000000000001}}]]},"title":{"type":"object","name":"Title","id":"p1183"},"renderers":[{"type":"object","name":"GlyphRenderer","id":"p1217","attributes":{"data_source":{"type":"object","name":"ColumnDataSource","id":"p1173","attributes":{"selected":{"type":"object","name":"Selection","id":"p1174","attributes":{"indices":[],"line_indices":[]}},"selection_policy":{"type":"object","name":"UnionRenderers","id":"p1175"},"data":{"type":"map","entries":[["x",{"type":"ndarray","array":{"type":"bytes","data":"AADABlYkeUIAAABZUC55QgAAgEX4N3lCAADAl/JBeUIAAADq7Et5QgAAAAvwVHlCAABAXepeeUI="},"shape":[7],"dtype":"float64","order":"little"}],["y",{"type":"ndarray","array":{"type":"bytes","data":"NTMzM7OdpUCXcD0KFzO3QNmjcD2KurhAmsL1KPySwUBrZmZmRjPAQFaPwvXojr9An5mZmdniwEA="},"shape":[7],"dtype":"float64","order":"little"}],["valor",["R$ 2766.85","R$ 5939.09","R$ 6330.54","R$ 8997.97","R$ 8294.55","R$ 8078.91","R$ 8645.70"]],["pedidos",{"type":"ndarray","array":{"type":"bytes","data":"pAAAADwCAAA7AgAASgMAAEYDAABVAwAATAMAAA=="},"shape":[7],"dtype":"int32","order":"little"}]]}}},"view":{"type":"object","name":"CDSView","id":"p1218","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1219"}}},"glyph":{"type":"object","name":"Line","id":"p1214","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"line_color":"#D0021B","line_width":2}},"nonselection_glyph":{"type":"object","name":"Line","id":"p1215","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"line_color":"#D0021B","line_alpha":0.1,"line_width":2}},"muted_glyph":{"type":"object","name":"Line","id":"p1216","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"line_color":"#D0021B","line_alpha":0.2,"line_width":2}}}},{"type":"object","name":"GlyphRenderer","id":"p1228","attributes":{"data_source":{"id":"p1173"},"view":{"type":"object","name":"CDSView","id":"p1229","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1230"}}},"glyph":{"type":"object","name":"Scatter","id":"p1225","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"size":{"type":"value","value":6},"line_color":{"type":"value","value":"#D0021B"},"fill_color":{"type":"value","value":"#D0021B"},"hatch_color":{"type":"value","value":"#D0021B"}}},"nonselection_glyph":{"type":"object","name":"Scatter","id":"p1226","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"size":{"type":"value","value":6},"line_color":{"type":"value","value":"#D0021B"},"line_alpha":{"type":"value","value":0.1},"fill_color":{"type":"value","value":"#D0021B"},"fill_alpha":{"type":"value","value":0.1},"hatch_color":{"type":"value","value":"#D0021B"},"hatch_alpha":{"type":"value","value":0.1}}},"muted_glyph":{"type":"object","name":"Scatter","id":"p1227","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"size":{"type":"value","value":6},"line_color":{"type":"value","value":"#D0021B"},"line_alpha":{"type":"value","value":0.2},"fill_color":{"type":"value","value":"#D0021B"},"fill_alpha":{"type":"value","value":0.2},"hatch_color":{"type":"value","value":"#D0021B"},"hatch_alpha":{"type":"value","value":0.2}}}}},{"type":"object","name":"GlyphRenderer","id":"p1242","attributes":{"y_range_name":"pedidos","data_source":{"id":"p1173"},"view":{"type":"object","name":"CDSView","id":"p1243","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1244"}}},"glyph":{"type":"object","name":"Line","id":"p1239","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":"gray","line_width":2}},"nonselection_glyph":{"type":"object","name":"Line","id":"p1240","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":"gray","line_alpha":0.1,"line_width":2}},"muted_glyph":{"type":"object","name":"Line","id":"p1241","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":"gray","line_alpha":0.2,"line_width":2}}}},{"type":"object","name":"GlyphRenderer","id":"p1252","attributes":{"y_range_name":"pedidos","data_source":{"id":"p1173"},"view":{"type":"object","name":"CDSView","id":"p1253","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1254"}}},"glyph":{"type":"object","name":"Scatter","id":"p1249","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":{"type":"value","value":"gray"},"hatch_color":{"type":"value","value":"gray"}}},"nonselection_glyph":{"type":"object","name":"Scatter","id":"p1250","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":{"type":"value","value":"gray"},"line_alpha":{"type":"value","value":0.1},"fill_alpha":{"type":"value","value":0.1},"hatch_color":{"type":"value","value":"gray"},"hatch_alpha":{"type":"value","value":0.1}}},"muted_glyph":{"type":"object","name":"Scatter","id":"p1251","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":{"type":"value","value":"gray"},"line_alpha":{"type":"value","value":0.2},"fill_alpha":{"type":"value","value":0.2},"hatch_color":{"type":"value","value":"gray"},"hatch_alpha":{"type":"value","value":0.2}}}}}],"toolbar":{"type":"object","name":"Toolbar","id":"p1184","attributes":{"tools":[{"type":"object","name":"HoverTool","id":"p1255","attributes":{"renderers":[{"id":"p1217"},{"id":"p1228"}],"tooltips":[["M\u00eas/Ano","@x{%b/%Y}"],["Pedidos","@pedidos"],["Comiss\u00e3o iFood (R$)","@valor"]],"formatters":{"type":"map","entries":[["@x","datetime"]]},"mode":"vline"}}]}},"toolbar_location":null,"left":[{"type":"object","name":"LinearAxis","id":"p1206","attributes":{"ticker":{"type":"object","name":"BasicTicker","id":"p1207","attributes":{"mantissas":[1,2,5]}},"formatter":{"type":"object","name":"BasicTickFormatter","id":"p1208"},"major_label_policy":{"type":"object","name":"AllLabels","id":"p1209"},"major_label_text_color":"#666666","major_label_text_font_size":"10pt","axis_line_color":null,"major_tick_line_color":null,"minor_tick_line_color":null}}],"right":[{"type":"object","name":"LinearAxis","id":"p1232","attributes":{"y_range_name":"pedidos","ticker":{"type":"object","name":"BasicTicker","id":"p1233","attributes":{"mantissas":[1,2,5]}},"formatter":{"type":"object","name":"BasicTickFormatter","id":"p1234"},"major_label_policy":{"type":"object","name":"AllLabels","id":"p1235"},"major_label_text_color":"#666666","major_label_text_font_size":"10pt","axis_line_color":null,"major_tick_line_color":null,"minor_tick_line_color":null}}],"below":[{"type":"object","name":"DatetimeAxis","id":"p1187","attributes":{"ticker":{"type":"object","name":"DatetimeTicker","id":"p1188","attributes":{"num_minor_ticks":5,"tickers":[{"type":"object","name":"AdaptiveTicker","id":"p1189","attributes":{"num_minor_ticks":0,"mantissas":[1,2,5],"max_interval":500.0}},{"type":"object","name":"AdaptiveTicker","id":"p1190","attributes":{"num_minor_ticks":0,"base":60,"mantissas":[1,2,5,10,15,20,30],"min_interval":1000.0,"max_interval":1800000.0}},{"type":"object","name":"AdaptiveTicker","id":"p1191","attributes":{"num_minor_ticks":0,"base":24,"mantissas":[1,2,4,6,8,12],"min_interval":3600000.0,"max_interval":43200000.0}},{"type":"object","name":"DaysTicker","id":"p1192","attributes":{"days":[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31]}},{"type":"object","name":"DaysTicker","id":"p1193","attributes":{"days":[1,4,7,10,13,16,19,22,25,28]}},{"type":"object","name":"DaysTicker","id":"p1194","attributes":{"days":[1,8,15,22]}},{"type":"object","name":"DaysTicker","id":"p1195","attributes":{"days":[1,15]}},{"type":"object","name":"MonthsTicker","id":"p1196","attributes":{"months":[0,1,2,3,4,5,6,7,8,9,10,11]}},{"type":"object","name":"MonthsTicker","id":"p1197","attributes":{"months":[0,2,4,6,8,10]}},{"type":"object","name":"MonthsTicker","id":"p1198","attributes":{"months":[0,4,8]}},{"type":"object","name":"MonthsTicker","id":"p1199","attributes":{"months":[0,6]}},{"type":"object","name":"YearsTicker","id":"p1200"}]}},"formatter":{"type":"object","name":"DatetimeTickFormatter","id":"p1203","attributes":{"seconds":"%T","minsec":"%T","minutes":"%H:%M","hours":"%H:%M","days":"%b %d","months":"%b %Y","strip_leading_zeros":["microseconds","milliseconds","seconds"],"boundary_scaling":false,"context":{"type":"object","name":"DatetimeTickFormatter","id":"p1202","attributes":{"microseconds":"%T","milliseconds":"%T","seconds":"%b %d, %Y","minsec":"%b %d, %Y","minutes":"%b %d, %Y","hourmin":"%b %d, %Y","hours":"%b %d, %Y","days":"%Y","months":"","years":"","boundary_scaling":false,"hide_repeats":true,"context":{"type":"object","name":"DatetimeTickFormatter","id":"p1201","attributes":{"microseconds":"%b %d, %Y","milliseconds":"%b %d, %Y","seconds":"","minsec":"","minutes":"","hourmin":"","hours":"","days":"","months":"","years":"","boundary_scaling":false,"hide_repeats":true}},"context_which":"all"}},"context_which":"all"}},"major_label_policy":{"type":"object","name":"AllLabels","id":"p1204"},"major_label_text_color":"#666666","major_label_text_font_size":"10pt","axis_line_color":null,"major_tick_line_color":null,"minor_tick_line_color":null}}],"center":[{"type":"object","name":"Grid","id":"p1205","attributes":{"axis":{"id":"p1187"},"grid_line_color":null}},{"type":"object","name":"Grid","id":"p1210","attributes":{"dimension":1,"axis":{"id":"p1206"},"grid_line_color":null}},{"type":"object","name":"Legend","id":"p1220","attributes":{"location":"bottom_center","orientation":"horizontal","border_line_color":null,"background_fill_color":null,"click_policy":"hide","label_text_font_size":"10pt","margin":0,"padding":5,"spacing":20,"items":[{"type":"object","name":"LegendItem","id":"p1221","attributes":{"label":{"type":"value","value":"Comiss\u00e3o iFood (R$)"},"renderers":[{"id":"p1217"},{"id":"p1228"}]}},{"type":"object","name":"LegendItem","id":"p1245","attributes":{"label":{"type":"value","value":"Contagem Pedidos"},"renderers":[{"id":"p1242"},{"id":"p1252"}]}}]}}]}}]}}
    </script>
    <script type="text/javascript">
      (function() {
        const fn = function() {
          Bokeh.safely(function() {
            (function(root) {
              function embed_document(root) {
              const docs_json = document.getElementById('ade63b60-612c-46fa-b352-f8b63986ccde').textContent;
              const render_items = [{"docid":"ceca7ddb-5efd-485d-bd46-c5ff8b230b9e","roots":{"p1176":"decc531c-8417-4324-8796-62a441a74fb7"},"root_ids":["p1176"]}];
              root.Bokeh.embed.embed_items(docs_json, render_items);
              }
              if (root.Bokeh !== undefined) {
                embed_document(root);
              } else {
                let attempts = 0;
                const timer = setInterval(function(root) {
                  if (root.Bokeh !== undefined) {
                    clearInterval(timer);
                    embed_document(root);
                  } else {
                    attempts++;
                    if (attempts > 100) {
                      clearInterval(timer);
                      console.log("Bokeh: ERROR: Unable to run BokehJS code because BokehJS library is missing");
                    }
                  }
                }, 10, root)
              }
            })(window);
          });
        };
        if (document.readyState != "loading") fn();
        else document.addEventListener("DOMContentLoaded", fn);
      })();
    </script>
  </body>
</html>
            </div><div class='grafico-container'>
                <h2 class="grafico-title">Comissão pagamento (R$) (mensal)</h2>
                <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Gráfico - Comissão pagamento (R$)</title>
    <style>
      html, body {
        box-sizing: border-box;
        display: flow-root;
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
    <script type="text/javascript" src="https://cdn.bokeh.org/bokeh/release/bokeh-3.7.2.min.js"></script>
    <script type="text/javascript">
        Bokeh.set_log_level("info");
    </script>
  </head>
  <body>
    <div id="e1fc5f65-b213-411e-a7ed-3d90e4980a77" data-root-id="p1262" style="display: contents;"></div>
  
    <script type="application/json" id="de451645-46df-4c7e-83ff-b7d613e271a5">
      {"4532c199-17f4-4a3e-8e53-5f48c10d3c7b":{"version":"3.7.2","title":"Bokeh Application","roots":[{"type":"object","name":"Figure","id":"p1262","attributes":{"name":"graph_vlr_comissao_pgto","height":300,"margin":[10,10,10,10],"x_range":{"type":"object","name":"DataRange1d","id":"p1263"},"y_range":{"type":"object","name":"DataRange1d","id":"p1264"},"x_scale":{"type":"object","name":"LinearScale","id":"p1271"},"y_scale":{"type":"object","name":"LinearScale","id":"p1272"},"extra_y_ranges":{"type":"map","entries":[["pedidos",{"type":"object","name":"Range1d","id":"p1317","attributes":{"start":147.6,"end":938.3000000000001}}]]},"title":{"type":"object","name":"Title","id":"p1269"},"renderers":[{"type":"object","name":"GlyphRenderer","id":"p1303","attributes":{"data_source":{"type":"object","name":"ColumnDataSource","id":"p1259","attributes":{"selected":{"type":"object","name":"Selection","id":"p1260","attributes":{"indices":[],"line_indices":[]}},"selection_policy":{"type":"object","name":"UnionRenderers","id":"p1261"},"data":{"type":"map","entries":[["x",{"type":"ndarray","array":{"type":"bytes","data":"AADABlYkeUIAAABZUC55QgAAgEX4N3lCAADAl/JBeUIAAADq7Et5QgAAAAvwVHlCAABAXepeeUI="},"shape":[7],"dtype":"float64","order":"little"}],["y",{"type":"ndarray","array":{"type":"bytes","data":"33oUrkcNeEAO16NwPeKJQMj1KFyPpItAUo/C9SiXk0CecD0K1wuSQJuZmZmZopFAjOtRuB7ukkA="},"shape":[7],"dtype":"float64","order":"little"}],["valor",["R$ 384.83","R$ 828.28","R$ 884.57","R$ 1253.79","R$ 1154.96","R$ 1128.65","R$ 1211.53"]],["pedidos",{"type":"ndarray","array":{"type":"bytes","data":"pAAAADwCAAA7AgAASgMAAEYDAABVAwAATAMAAA=="},"shape":[7],"dtype":"int32","order":"little"}]]}}},"view":{"type":"object","name":"CDSView","id":"p1304","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1305"}}},"glyph":{"type":"object","name":"Line","id":"p1300","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"line_color":"#9013FE","line_width":2}},"nonselection_glyph":{"type":"object","name":"Line","id":"p1301","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"line_color":"#9013FE","line_alpha":0.1,"line_width":2}},"muted_glyph":{"type":"object","name":"Line","id":"p1302","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"line_color":"#9013FE","line_alpha":0.2,"line_width":2}}}},{"type":"object","name":"GlyphRenderer","id":"p1314","attributes":{"data_source":{"id":"p1259"},"view":{"type":"object","name":"CDSView","id":"p1315","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1316"}}},"glyph":{"type":"object","name":"Scatter","id":"p1311","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"size":{"type":"value","value":6},"line_color":{"type":"value","value":"#9013FE"},"fill_color":{"type":"value","value":"#9013FE"},"hatch_color":{"type":"value","value":"#9013FE"}}},"nonselection_glyph":{"type":"object","name":"Scatter","id":"p1312","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"size":{"type":"value","value":6},"line_color":{"type":"value","value":"#9013FE"},"line_alpha":{"type":"value","value":0.1},"fill_color":{"type":"value","value":"#9013FE"},"fill_alpha":{"type":"value","value":0.1},"hatch_color":{"type":"value","value":"#9013FE"},"hatch_alpha":{"type":"value","value":0.1}}},"muted_glyph":{"type":"object","name":"Scatter","id":"p1313","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"y"},"size":{"type":"value","value":6},"line_color":{"type":"value","value":"#9013FE"},"line_alpha":{"type":"value","value":0.2},"fill_color":{"type":"value","value":"#9013FE"},"fill_alpha":{"type":"value","value":0.2},"hatch_color":{"type":"value","value":"#9013FE"},"hatch_alpha":{"type":"value","value":0.2}}}}},{"type":"object","name":"GlyphRenderer","id":"p1328","attributes":{"y_range_name":"pedidos","data_source":{"id":"p1259"},"view":{"type":"object","name":"CDSView","id":"p1329","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1330"}}},"glyph":{"type":"object","name":"Line","id":"p1325","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":"gray","line_width":2}},"nonselection_glyph":{"type":"object","name":"Line","id":"p1326","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":"gray","line_alpha":0.1,"line_width":2}},"muted_glyph":{"type":"object","name":"Line","id":"p1327","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":"gray","line_alpha":0.2,"line_width":2}}}},{"type":"object","name":"GlyphRenderer","id":"p1338","attributes":{"y_range_name":"pedidos","data_source":{"id":"p1259"},"view":{"type":"object","name":"CDSView","id":"p1339","attributes":{"filter":{"type":"object","name":"AllIndices","id":"p1340"}}},"glyph":{"type":"object","name":"Scatter","id":"p1335","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":{"type":"value","value":"gray"},"hatch_color":{"type":"value","value":"gray"}}},"nonselection_glyph":{"type":"object","name":"Scatter","id":"p1336","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":{"type":"value","value":"gray"},"line_alpha":{"type":"value","value":0.1},"fill_alpha":{"type":"value","value":0.1},"hatch_color":{"type":"value","value":"gray"},"hatch_alpha":{"type":"value","value":0.1}}},"muted_glyph":{"type":"object","name":"Scatter","id":"p1337","attributes":{"x":{"type":"field","field":"x"},"y":{"type":"field","field":"pedidos"},"line_color":{"type":"value","value":"gray"},"line_alpha":{"type":"value","value":0.2},"fill_alpha":{"type":"value","value":0.2},"hatch_color":{"type":"value","value":"gray"},"hatch_alpha":{"type":"value","value":0.2}}}}}],"toolbar":{"type":"object","name":"Toolbar","id":"p1270","attributes":{"tools":[{"type":"object","name":"HoverTool","id":"p1341","attributes":{"renderers":[{"id":"p1303"},{"id":"p1314"}],"tooltips":[["M\u00eas/Ano","@x{%b/%Y}"],["Pedidos","@pedidos"],["Comiss\u00e3o pagamento (R$)","@valor"]],"formatters":{"type":"map","entries":[["@x","datetime"]]},"mode":"vline"}}]}},"toolbar_location":null,"left":[{"type":"object","name":"LinearAxis","id":"p1292","attributes":{"ticker":{"type":"object","name":"BasicTicker","id":"p1293","attributes":{"mantissas":[1,2,5]}},"formatter":{"type":"object","name":"BasicTickFormatter","id":"p1294"},"major_label_policy":{"type":"object","name":"AllLabels","id":"p1295"},"major_label_text_color":"#666666","major_label_text_font_size":"10pt","axis_line_color":null,"major_tick_line_color":null,"minor_tick_line_color":null}}],"right":[{"type":"object","name":"LinearAxis","id":"p1318","attributes":{"y_range_name":"pedidos","ticker":{"type":"object","name":"BasicTicker","id":"p1319","attributes":{"mantissas":[1,2,5]}},"formatter":{"type":"object","name":"BasicTickFormatter","id":"p1320"},"major_label_policy":{"type":"object","name":"AllLabels","id":"p1321"},"major_label_text_color":"#666666","major_label_text_font_size":"10pt","axis_line_color":null,"major_tick_line_color":null,"minor_tick_line_color":null}}],"below":[{"type":"object","name":"DatetimeAxis","id":"p1273","attributes":{"ticker":{"type":"object","name":"DatetimeTicker","id":"p1274","attributes":{"num_minor_ticks":5,"tickers":[{"type":"object","name":"AdaptiveTicker","id":"p1275","attributes":{"num_minor_ticks":0,"mantissas":[1,2,5],"max_interval":500.0}},{"type":"object","name":"AdaptiveTicker","id":"p1276","attributes":{"num_minor_ticks":0,"base":60,"mantissas":[1,2,5,10,15,20,30],"min_interval":1000.0,"max_interval":1800000.0}},{"type":"object","name":"AdaptiveTicker","id":"p1277","attributes":{"num_minor_ticks":0,"base":24,"mantissas":[1,2,4,6,8,12],"min_interval":3600000.0,"max_interval":43200000.0}},{"type":"object","name":"DaysTicker","id":"p1278","attributes":{"days":[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31]}},{"type":"object","name":"DaysTicker","id":"p1279","attributes":{"days":[1,4,7,10,13,16,19,22,25,28]}},{"type":"object","name":"DaysTicker","id":"p1280","attributes":{"days":[1,8,15,22]}},{"type":"object","name":"DaysTicker","id":"p1281","attributes":{"days":[1,15]}},{"type":"object","name":"MonthsTicker","id":"p1282","attributes":{"months":[0,1,2,3,4,5,6,7,8,9,10,11]}},{"type":"object","name":"MonthsTicker","id":"p1283","attributes":{"months":[0,2,4,6,8,10]}},{"type":"object","name":"MonthsTicker","id":"p1284","attributes":{"months":[0,4,8]}},{"type":"object","name":"MonthsTicker","id":"p1285","attributes":{"months":[0,6]}},{"type":"object","name":"YearsTicker","id":"p1286"}]}},"formatter":{"type":"object","name":"DatetimeTickFormatter","id":"p1289","attributes":{"seconds":"%T","minsec":"%T","minutes":"%H:%M","hours":"%H:%M","days":"%b %d","months":"%b %Y","strip_leading_zeros":["microseconds","milliseconds","seconds"],"boundary_scaling":false,"context":{"type":"object","name":"DatetimeTickFormatter","id":"p1288","attributes":{"microseconds":"%T","milliseconds":"%T","seconds":"%b %d, %Y","minsec":"%b %d, %Y","minutes":"%b %d, %Y","hourmin":"%b %d, %Y","hours":"%b %d, %Y","days":"%Y","months":"","years":"","boundary_scaling":false,"hide_repeats":true,"context":{"type":"object","name":"DatetimeTickFormatter","id":"p1287","attributes":{"microseconds":"%b %d, %Y","milliseconds":"%b %d, %Y","seconds":"","minsec":"","minutes":"","hourmin":"","hours":"","days":"","months":"","years":"","boundary_scaling":false,"hide_repeats":true}},"context_which":"all"}},"context_which":"all"}},"major_label_policy":{"type":"object","name":"AllLabels","id":"p1290"},"major_label_text_color":"#666666","major_label_text_font_size":"10pt","axis_line_color":null,"major_tick_line_color":null,"minor_tick_line_color":null}}],"center":[{"type":"object","name":"Grid","id":"p1291","attributes":{"axis":{"id":"p1273"},"grid_line_color":null}},{"type":"object","name":"Grid","id":"p1296","attributes":{"dimension":1,"axis":{"id":"p1292"},"grid_line_color":null}},{"type":"object","name":"Legend","id":"p1306","attributes":{"location":"bottom_center","orientation":"horizontal","border_line_color":null,"background_fill_color":null,"click_policy":"hide","label_text_font_size":"10pt","margin":0,"padding":5,"spacing":20,"items":[{"type":"object","name":"LegendItem","id":"p1307","attributes":{"label":{"type":"value","value":"Comiss\u00e3o pagamento (R$)"},"renderers":[{"id":"p1303"},{"id":"p1314"}]}},{"type":"object","name":"LegendItem","id":"p1331","attributes":{"label":{"type":"value","value":"Contagem Pedidos"},"renderers":[{"id":"p1328"},{"id":"p1338"}]}}]}}]}}]}}
    </script>
    <script type="text/javascript">
      (function() {
        const fn = function() {
          Bokeh.safely(function() {
            (function(root) {
              function embed_document(root) {
              const docs_json = document.getElementById('de451645-46df-4c7e-83ff-b7d613e271a5').textContent;
              const render_items = [{"docid":"4532c199-17f4-4a3e-8e53-5f48c10d3c7b","roots":{"p1262":"e1fc5f65-b213-411e-a7ed-3d90e4980a77"},"root_ids":["p1262"]}];
              root.Bokeh.embed.embed_items(docs_json, render_items);
              }
              if (root.Bokeh !== undefined) {
                embed_document(root);
              } else {
                let attempts = 0;
                const timer = setInterval(function(root) {
                  if (root.Bokeh !== undefined) {
                    clearInterval(timer);
                    embed_document(root);
                  } else {
                    attempts++;
                    if (attempts > 100) {
                      clearInterval(timer);
                      console.log("Bokeh: ERROR: Unable to run BokehJS code because BokehJS library is missing");
                    }
                  }
                }, 10, root)
              }
            })(window);
          });
        };
        if (document.readyState != "loading") fn();
        else document.addEventListener("DOMContentLoaded", fn);
      })();
    </script>
  </body>
</html>
            </div>
        </div>
        
        <div class="matriz-container">
            <h2 class="text-xl font-bold text-gray-700 mb-4">Análise Mensal de Pedidos e Descontos</h2>
            <table border="1" class="dataframe min-w-full bg-white rounded-lg overflow-hidden shadow-lg" id="matriz-ifood">
  <thead>
    <tr style="text-align: right;">
      <th>Ano/Mês</th>
      <th>Qtd Pedidos</th>
      <th>Valor Total</th>
      <th>Valor Desconto</th>
      <th>% Desconto</th>
      <th>Valor Entrega</th>
      <th>% Entrega</th>
      <th>Valor Comissão</th>
      <th>% Comissão</th>
      <th>Total Descontos</th>
      <th>% Total</th>
      <th>Faturamento Líquido</th>
      <th>% Líquido</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>2024-10</td>
      <td>0</td>
      <td>R$ 675.50</td>
      <td>R$ 0.00</td>
      <td>0.0%</td>
      <td>R$ 0.00</td>
      <td>0.0%</td>
      <td>R$ 0.00</td>
      <td>0.0%</td>
      <td>R$ 0.00</td>
      <td>0.0%</td>
      <td>R$ 99.90</td>
      <td>14.8%</td>
    </tr>
    <tr>
      <td>2024-11</td>
      <td>631</td>
      <td>R$ 34,380.75</td>
      <td>R$ -11,166.67</td>
      <td>-32.5%</td>
      <td>R$ -6,455.73</td>
      <td>-18.8%</td>
      <td>R$ -7,438.94</td>
      <td>-21.6%</td>
      <td>R$ -25,061.34</td>
      <td>-72.9%</td>
      <td>R$ 12,598.91</td>
      <td>36.6%</td>
    </tr>
    <tr>
      <td>2024-12</td>
      <td>646</td>
      <td>R$ 36,930.10</td>
      <td>R$ -12,809.97</td>
      <td>-34.7%</td>
      <td>R$ -7,020.39</td>
      <td>-19.0%</td>
      <td>R$ -7,866.27</td>
      <td>-21.3%</td>
      <td>R$ -27,696.63</td>
      <td>-75.0%</td>
      <td>R$ 13,217.13</td>
      <td>35.8%</td>
    </tr>
    <tr>
      <td>2025-01</td>
      <td>916</td>
      <td>R$ 52,263.10</td>
      <td>R$ -18,670.43</td>
      <td>-35.7%</td>
      <td>R$ -9,882.84</td>
      <td>-18.9%</td>
      <td>R$ -11,037.76</td>
      <td>-21.1%</td>
      <td>R$ -39,591.03</td>
      <td>-75.8%</td>
      <td>R$ 18,875.07</td>
      <td>36.1%</td>
    </tr>
    <tr>
      <td>2025-02</td>
      <td>916</td>
      <td>R$ 48,384.10</td>
      <td>R$ -16,847.17</td>
      <td>-34.8%</td>
      <td>R$ -8,707.80</td>
      <td>-18.0%</td>
      <td>R$ -10,162.95</td>
      <td>-21.0%</td>
      <td>R$ -35,717.92</td>
      <td>-73.8%</td>
      <td>R$ 17,981.52</td>
      <td>37.2%</td>
    </tr>
    <tr>
      <td>2025-03</td>
      <td>937</td>
      <td>R$ 47,939.50</td>
      <td>R$ -16,946.71</td>
      <td>-35.4%</td>
      <td>R$ -8,876.73</td>
      <td>-18.5%</td>
      <td>R$ -10,050.17</td>
      <td>-21.0%</td>
      <td>R$ -35,873.61</td>
      <td>-74.8%</td>
      <td>R$ 16,928.62</td>
      <td>35.3%</td>
    </tr>
    <tr>
      <td>2025-04</td>
      <td>937</td>
      <td>R$ 51,362.60</td>
      <td>R$ -18,538.57</td>
      <td>-36.1%</td>
      <td>R$ -9,589.65</td>
      <td>-18.7%</td>
      <td>R$ -10,710.17</td>
      <td>-20.9%</td>
      <td>R$ -38,838.39</td>
      <td>-75.6%</td>
      <td>R$ 17,959.03</td>
      <td>35.0%</td>
    </tr>
  </tbody>
</table>
        </div>
    </main>
</body>
</html>
