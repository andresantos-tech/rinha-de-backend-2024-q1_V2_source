# Submissão para Rinha de Backend, Segunda Edição: Controle de Concorrência (source)

<img src="./images/nginx.svg" alt="logo nginx" width="150" height="auto" align="left" style="margin: 38px 30px 0 0; ">
<img src="./images/php.svg" alt="logo PHP" width="150" height="auto" align="left" style="margin: 15px 30px 0 0;" />
<img src="./images/postgres.svg" alt="logo postgres" width="100" height="auto" >

<img src="./images/RoadRunner.png" alt="logo RoadRunner" width="200" height="auto" align="left" style="margin: 21px 30px 0 0;" />
<br>

Nova submissão para testar quatro coisas diferentes [do que tinha feito antes](https://github.com/zanfranceschi/rinha-de-backend-2024-q1/tree/main/participantes/andresantos-tech-PHP):
- Impacto de performance ao remover o web framework [Spiral](https://spiral.dev/) [baseado na submissão do Gianluca Bine (Pr3d4dor)]
- Uso de 5 workers do RoadRunner por API ao invés de 1
- Impacto de performance por mover as regras de validação do saldo para dentro do Postgres [baseado na submissão do @giovannibassi]
- Atualização do PHP 8.2 para 8.3 (o tempo de resposta das validações aumentou um pouco, mas decidi manter assim)

Também tentei fazer um "warmup" da aplicação (simulando X requisições antes do teste começar) mas não rolou. Fica pra próxima rinha :)

## 🚀 Como rodar o projeto (source)
Basta buildar a imagem e subir o container:
```
docker compose build rinha_v2_app1
docker compose up
```
**Importante:** Na primeira vez que rodar, as dependências do composer serão instaladas e ao término disso será criado um arquivo `composer-installed` na raiz do projeto. **Não remova esse arquivo :)**

O motivo disso é que, como existem duas APIs, as dependências são instaladas à partir do `rinha_v2_app1` enquanto o `rinha_v2_app2` "espera" a conclusão da instalação (que é finalizada com a criação do arquivo `composer-installed`). Dá pra ver a lógica disso no arquivo .docker/app/entrypoint.sh.

## 💻 Tecnologias utilizadas
- [`nginx`](https://www.nginx.com/) (load balancer)
- [`postgres`](https://www.postgresql.org/) (banco de dados)
- [`php`](https://www.php.net/) (linguagem)
- [`roadrunner`](https://roadrunner.dev/) (application server)

## 💾 Repositório
- [andresantos-tech / **rinha-de-backend-2024-q1_V2_source**](https://github.com/andresantos-tech/rinha-de-backend-2024-q1_V2_source/)

<hr>

### Desenvolvido por: André Santos
[![twitter/X](https://img.shields.io/badge/Twitter-000000?style=for-the-badge&logo=X&logoColor=white)](https://github.com/andresantos-tech)
[![linkedin](https://img.shields.io/badge/LinkedIn-0077B5?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/andresantos-tech/)
[![github](https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white)](https://github.com/andresantos-tech)




