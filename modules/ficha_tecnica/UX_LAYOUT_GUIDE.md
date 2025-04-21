# 🎨 Guia de Layout, Cores e UX – Projeto Ficha Técnica

Este documento orienta o estilo visual, uso de cores e experiência de usuário (UX) adotados no sistema, para que futuros desenvolvedores mantenham a mesma identidade visual em novos módulos ou versões.

---

## 🎯 Princípios de Design

- **Minimalista:** Interface limpa, sem poluição visual  
- **Responsivo:** Adapta-se perfeitamente do mobile ao desktop  
- **Dark Mode:** Toda interface segue tema escuro para foco e contraste  
- **Acessível:** Cores com contraste alto, tipografia legível, botões com espaçamento adequado  

---

## 🎨 Paleta de Cores

| Nome           | Hexadecimal   | Uso Principal                                 |
|----------------|---------------|-----------------------------------------------|
| Fundo escuro   | `#1F2937`     | Fundo da interface (`bg-gray-900`)            |
| Containers     | `#374151`     | Blocos internos (`bg-gray-800`)               |
| Texto neutro   | `#E5E7EB`     | Texto padrão (`text-gray-100`)                |
| Ciano          | `#06B6D4`     | Ações primárias (botões, títulos, bordas)     |
| Roxo claro     | `#A78BFA`     | Ações de histórico                            |
| Verde limão    | `#22C55E`     | Ações de confirmação / botões positivos       |
| Vermelho       | `#EF4444`     | Ações de exclusão / alertas                   |

---

## 🔘 Componentes

### Botões

- Padding vertical `py-3`
- Bordas arredondadas (`rounded`)
- Transição hover (`hover:bg-*`)
- Fonte branca (`text-white`)
- Largura mínima: `min-w-[170px]` para alinhamento

```html
<a class="bg-cyan-500 hover:bg-cyan-600 text-white px-6 py-3 rounded shadow">
  Ação
</a>

📱 Cards Responsivos (modo mobile)
Usados em substituição à tabela tradicional quando em telas pequenas

Aparecem com md:hidden, mantendo acessibilidade total em mobile

Estrutura:

Container com bg-gray-800, p-4, rounded, shadow-md

Título destacado (text-cyan-400, font-semibold)

Labels com text-sm text-gray-300

Ações em links pequenos com cores específicas (cyan, yellow, purple, red)

<div class="bg-gray-800 p-4 rounded shadow-md">
  <div class="flex justify-between mb-2">
    <h2 class="text-cyan-400 font-semibold">Nome do Prato</h2>
    <span class="text-sm text-gray-400">#ID</span>
  </div>
  <p class="text-sm text-gray-300"><strong>Rendimento:</strong> valor</p>
  <div class="mt-3 flex gap-2 text-sm">
    <a href="#" class="text-cyan-400 hover:underline">Ver</a>
    <a href="#" class="text-yellow-400 hover:underline">Editar</a>
    <a href="#" class="text-purple-400 hover:underline">Histórico</a>
    <a href="#" class="text-red-500 hover:underline">Excluir</a>
  </div>
</div>

📐 Responsividade de Componentes
📌 Exibição condicional por tamanho de tela
Use Tailwind utilities como hidden md:block e md:hidden para alternar entre versões mobile e desktop dos componentes (como cards e tabelas).


📦 Versão: v1.5.2
🛠️ Última atualização: Cards responsivos e layout adaptativo (abril/2025)