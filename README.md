🌟 W1 Consultoria | Site de Consultoria Patrimonial (Hackathon 2025)
Introdução

Bem-vindo ao repositório do nosso projeto desenvolvido no Hackathon W1 Consultoria 2025! Neste desafio, nossa equipe criou um site de consultoria patrimonial funcional para ajudar a organizar e visualizar seus investimentos.
Hackathon W1 Consultoria 2025 🎉

    Nome do Hackathon: Desafio W1 Consultoria

    Ano: 2025

    Objetivo: Construir um site de consultoria patrimonial completo e intuitivo para usuários finais, integrando front-end e back-end em uma solução unificada.

Nossa equipe participou com muita energia e criatividade, colocando em prática as melhores práticas de desenvolvimento para entregar um produto 90% funcional e de alta qualidade.
Equipe 🚀

Nossa equipe foi composta por:

    Lean Lopes 👨‍💻

    Giovanna Cenciarelli 👩‍💻

    Derick Mazelli 👨‍💻

Trabalhamos juntos do conceito ao código, unindo habilidades em front-end e back-end para tornar este projeto uma realidade.
Tecnologias Utilizadas 🛠️

Utilizamos uma stack variada e moderna para entregar performance e usabilidade:

    🟧 HTML5 – estrutura e semântica do conteúdo.

    🎨 CSS3 – estilos e responsividade.

    ⚙️ JavaScript – interatividade no front-end.

    🐘 PHP – lógica de servidor e integração com banco de dados.

    🐬 MySQL – banco de dados relacional para armazenar informações financeiras.

    🐍 Python – scripts auxiliares e processamento de dados.

Instalação e Execução ⚙️

Para executar o projeto localmente, siga os passos abaixo:

    Clone este repositório:

    git clone https://github.com/seu-usuario/seu-repositorio.git

    Configure o ambiente de servidor:

        Instale e configure um servidor local (por exemplo, XAMPP ou WAMP).

        Coloque os arquivos do projeto dentro da pasta htdocs ou www do servidor.

    Banco de Dados:

        Crie um banco de dados no MySQL (ex: site_w1).

        Tabela "usuario" com id (primária), nome, email, senha, genero
        Tabela "dados_patri" com id (primária), user_id (estrangeira da usuario), qtd_viagens, qtd_aposentadoria, qtd_casamento, qtd_filhos, qtd_bolsa, qtd_fundInves, qtd_bdrs, qtd_crypto 	
        Tabela "patrimony_snapshots" com id (primária), user_id (estrangeira da dados_patri), timestamp, patrimonio_liquido, total_rendimentos, max_alta_ativo,	max_alta_valor,	max_baixa_ativo, max_baixa_valor, rentabilidade_total_percentual 	
        Tabela "patrimony_allocations" com id (primária), snapshot_id (estrangeira da patrimony_snapshots), ativo, tipo, valor_investido, rendimento_percentual, valor_atual 	

    Execute o back-end:

        As páginas PHP serão executadas automaticamente pelo servidor local (abra http://localhost/index.html no navegador).

        Para o python precisa das seguintes bibliotecas: pip install mysql-connector-python yfinance schedule

    Acesse o site:

        Navegue até http://localhost no seu navegador preferido e aproveite as funcionalidades do site de consultoria.

📝 Dica: Consulte os comentários no código para mais informações sobre configuração específica.
Funcionalidades Principais ✨

O site oferece diversas funcionalidades para aprimorar a experiência do usuário:

    📊 Dashboard de Investimentos: Visualize gráficos dinâmicos do patrimônio e compare desempenho ao longo do tempo.

    🔐 Sistema de Login Seguro: Autenticação de usuários com diferentes níveis de acesso (clientes e administradores).

    📝 Gerenciamento de Portfólio: Adicione, remova e edite ativos financeiros, como ações, fundos e criptomoedas.

    📈 Simulador de Investimentos: Ferramenta interativa para projetar cenários e avaliar potenciais retornos.

    📑 Relatórios Personalizados: Gere relatórios detalhados em PDF para exportar e compartilhar insights financeiros.

    📱 Layout Responsivo: Interface adaptável para desktop e dispositivos móveis, garantindo acessibilidade em qualquer lugar.

Cada funcionalidade foi desenvolvida com foco em usabilidade e estética moderna, tornando a experiência do usuário agradável e intuitiva.
Estrutura do Projeto 📂

Vídeo Demonstrativo 🎥


https://github.com/user-attachments/assets/06931a70-3d82-4d1c-83a8-950165ad2b93


Confira o vídeo demonstrativo para ver todas as funcionalidades em ação! O vídeo está localizado na pasta docs/ do repositório (arquivo demo.mp4). Ele apresenta uma visão geral do sistema, navegação pelas principais páginas e exemplos de uso dos recursos.
Créditos e Agradecimentos 🏆

    Desenvolvedores: Lean Lopes, Giovanna Cenciarelli e Derick Mazelli.

    Agradecemos à W1 Consultoria pelo suporte e pelo hackathon inspirador.

    Este projeto foi desenvolvido durante o Hackathon W1 Consultoria 2025, onde pudemos aprender, inovar e colaborar em equipe.

❤️ Feito com paixão e código! Obrigado por explorar nosso projeto. Sinta-se à vontade para contribuir e sugerir melhorias.
