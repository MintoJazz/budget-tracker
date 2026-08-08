Mapear os requisitos de forma clara agora vai ser a bússola para o desenvolvimento e também a base do capítulo de Metodologia/Desenvolvimento do documento da sua graduação em Análise e Desenvolvimento de Sistemas.

Aqui está o mapeamento dos requisitos e o roteiro de estudos focado no seu escopo.

### 1. Requisitos Funcionais (RF)

O que o sistema _deve fazer_ para o usuário final.

| ID       | Requisito                             | Descrição                                                                                                                                                    |
| -------- | ------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **RF01** | **Gestão do Plano de Contas**         | O sistema deve permitir criar, ler, atualizar e inativar contas contábeis em uma estrutura de árvore (níveis hierárquicos).                                  |
| **RF02** | **Registro de Lançamentos**           | O sistema deve permitir o registro de transações, exigindo obrigatoriamente múltiplas entradas (débitos e créditos) que fechem o saldo da transação em zero. |
| **RF03** | **Consulta de Movimentações (Razão)** | O sistema deve exibir o extrato detalhado de todas as movimentações (entradas e saídas) de uma conta específica em um determinado período.                   |
| **RF04** | **Geração de Balancete**              | O sistema deve calcular e exibir um relatório contendo os saldos iniciais, débitos, créditos e saldos finais de todas as contas no período selecionado.      |
| **RF05** | **Geração de DRE**                    | O sistema deve gerar a Demonstração do Resultado do Exercício consolidando receitas e despesas para apurar lucro ou prejuízo do período.                     |
| **RF06** | **Controle de Acesso (RBAC)**         | O sistema deve possuir níveis de permissão (ex: apenas leitura para auditores, criação para analistas e gestão total para administradores).                  |

---

### 2. Requisitos Não Funcionais (RNF)

Como o sistema _deve se comportar_ em termos de arquitetura, tecnologia e segurança.

| ID        | Requisito                             | Descrição                                                                                                                                                                                                |
| --------- | ------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **RNF01** | **Arquitetura Tecnológica**           | O sistema deve ser construído em PHP 8+ com o framework Laravel e o painel de administração em Filament.                                                                                                 |
| **RNF02** | **Persistência e Integridade (ACID)** | O banco de dados (ex: PostgreSQL) deve utilizar transações estritas (`DB::transaction`) para garantir que lançamentos de débito e crédito ocorram atomicamente. Se um falhar, ocorre _rollback_ de tudo. |
| **RNF03** | **Tratamento de Concorrência**        | A atualização do saldo das contas deve utilizar _Pessimistic Locking_ (ex: `lockForUpdate()` no Laravel) para evitar _Race Conditions_ em transações simultâneas.                                        |
| **RNF04** | **Imutabilidade de Dados**            | Um lançamento contábil, uma vez processado, não pode ser editado (UPDATE) ou apagado (DELETE) no banco. Correções só podem ser feitas via "estorno" (um novo lançamento inverso).                        |

---

### 3. O que estudar de Conceitos da Área

Para desenvolver o sistema e, principalmente, ter domínio e segurança durante a defesa perante a banca, você precisará dividir seus estudos entre as regras de negócio e a engenharia de software.

#### Domínio de Negócio (Contabilidade Básica)

Você não precisa ser contador, mas precisa dominar a linguagem deles.

- **Natureza das Contas (Devedora vs. Credora):** Este é o conceito que mais trava os desenvolvedores. Em contabilidade, "Débito" nem sempre diminui e "Crédito" nem sempre aumenta. Contas de **Ativo** (dinheiro em caixa) aumentam com débito. Contas de **Passivo** (dívidas) aumentam com crédito. Entender essa inversão matemática é o coração do seu motor financeiro.
- **Estrutura de um Plano de Contas:** Como as contas são numeradas e categorizadas (ex: `1.` Ativo, `1.1.` Ativo Circulante, `1.1.1.` Caixa e Bancos).
- **A diferença entre Estorno e Exclusão:** As normas de auditoria e _compliance_ financeiro exigem um histórico perfeito (_audit trail_).

#### Engenharia e Arquitetura de Software

Como aplicar os conceitos acima dentro do ecossistema Laravel.

- **Padrões de Arquitetura no Laravel (_Action Classes_ ou _Services_):** O Filament é fantástico para as telas, mas a lógica de validação das partidas dobradas (verificar se Débitos == Créditos) não pode ficar solta nos _Resources_ do Filament nem nos _Observers_ dos _Models_. Ela precisa estar em uma camada de serviço isolada e testável.
- **Database Locks (_Pessimistic e Optimistic Locking_):** Estude como o seu banco de dados lida com concorrência e como o Eloquent ORM implementa isso. O que acontece se duas requisições tentarem debitar a mesma conta ao mesmo tempo?
- **Design Pattern: Ledger Architecture / Event Sourcing (O Básico):** Estude como sistemas bancários armazenam dados. Eles geralmente registram "eventos" de movimentação (tabela _ledger_) em vez de ficar atualizando uma coluna de "saldo_atual" diretamente em uma tabela de usuários.
