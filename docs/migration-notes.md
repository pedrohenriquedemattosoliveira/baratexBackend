# Notas de migração (Java/Spring Boot → PHP/Laravel)

Registro das decisões de design e dos pontos em que este porte
**diverge deliberadamente** do comportamento da API original — seja por
corrigir um bug, seja por adaptar algo ao idioma do Laravel.

## Correções de bugs herdados

1. **Busca de oficinas próximas sempre vazia.** O campo `localizacao`
   (geometry) em `Endereco.java` nunca era preenchido em nenhum fluxo de
   cadastro, então `ST_DWithin` nunca encontrava nada. Aqui a busca usa
   direto `latitude`/`longitude` (fórmula de Haversine em SQL), que são
   sempre preenchidas no cadastro — nunca fica vazia por esse motivo.
   Ver [docs/mysql.md](mysql.md).

2. **`Authentication#getName()` não retornava o e-mail.** Em
   `VeiculoController.java`, o código assumia que
   `SecurityContextHolder...getAuthentication().getName()` retornava o
   e-mail do usuário autenticado — mas como `Usuario` não implementa
   `UserDetails`, esse método na prática devolvia
   `Usuario.toString()` (o endereço de memória do objeto), fazendo
   `cadastrarVeiculo`/`listarMeusVeiculos` falharem com "usuário não
   encontrado" sempre que chamados. Aqui, o guard JWT
   (`App\Auth\JwtGuard`) resolve e expõe o model `Usuario` completo via
   `$request->user()`, então `->email` e `->id` sempre funcionam.

3. **Cadastro de usuário sem CPF persistia mesmo retornando erro.** Em
   `AuthController.java`, o método `register` tinha `@Transactional`,
   mas retornava `ResponseEntity.badRequest()` (sem lançar exceção)
   quando faltava o CPF de um motorista — como `@Transactional` só
   desfaz em caso de exceção, o `Usuario` ficava salvo no banco mesmo
   com a resposta de erro. Aqui, a ausência do CPF lança
   `NegocioException` dentro da transação, desfazendo o cadastro por
   completo (`App\Services\AuthService::registrar`).

## Adaptações de arquitetura

4. **WebSocket STOMP/SockJS → Laravel Broadcasting (Reverb).** Ver
   [docs/websockets.md](websockets.md).

5. **Resposta "flat" em vez de objetos JPA aninhados.** A API original
   devolvia as entidades JPA diretamente em alguns endpoints (ex:
   `Agendamento`, com os objetos completos de `oficina` e `veiculo`
   aninhados, graças ao fetch EAGER padrão do `@ManyToOne`). Aqui, os
   endpoints equivalentes devolvem `oficinaId`/`veiculoId` (chaves
   estrangeiras simples) via `App\Http\Resources\AgendamentoResource`,
   no mesmo estilo dos demais DTOs de resposta da própria API original
   (`OficinaResponseDTO`, `VeiculoResponseDTO` etc.) — mais consistente
   e sem o risco de vazar dados sensíveis (ex: `senha_hash`) por
   serialização automática de relacionamentos.

6. **Nomes de tabela e coluna preservados.** Os nomes de tabela da API
   original foram mantidos (inclusive a peculiaridade `tb_veiculo`, em
   vez de `veiculos`), assim como os nomes de coluna em `snake_case` já
   usados via `@Column(name = ...)`. Isso facilita migrar dados de um
   banco já existente da API Java para esta API Laravel sem precisar de
   um script de transformação.

9. **PostgreSQL + PostGIS → MySQL/MariaDB puro.** A API original
   dependia do PostGIS (`ST_DWithin`) para a busca de oficinas
   próximas. Como o alvo de implantação deste porte é MySQL (XAMPP), a
   busca por proximidade foi reescrita com a fórmula de Haversine em
   SQL puro — sem nenhuma extensão espacial. Ver
   [docs/mysql.md](mysql.md). A coluna `localizacao` (geometry) da
   entidade `Endereco.java` não existe mais: o campo `localizacao` no
   JSON de resposta continua existindo (GeoJSON), mas agora é só uma
   representação calculada de `latitude`/`longitude`, nunca persistida
   como geometria no banco.

10. **Endpoints "/me", "/minhas" e "/minha-oficina" (novos, sem equivalente
    na API original).** Adicionados para o app mobile (`baratex-mobile`)
    conseguir resolver os próprios dados a partir do JWT, sem precisar
    conhecer de antemão o UUID do motorista/oficina do usuário logado:
    `GET /api/motoristas/me`, `GET /api/oficinas/me`,
    `GET /api/agendamentos/minhas` (do motorista autenticado) e
    `GET /api/agendamentos/minha-oficina` (da oficina autenticada).
    Seguem o mesmo padrão self-scoped já usado por `GET /api/veiculos`.

## Pontos mantidos de propósito (podem parecer estranhos, mas são fiéis ao original)

7. **`POST /api/oficinas`, `POST /api/servicos` e `POST /api/motoristas`
   exigem JWT.** Isso reflete fielmente a regra
   `anyRequest().authenticated()` do `SecurityConfig.java` original: só
   `/api/auth/**` e os `GET` de oficinas/serviços/modelos são públicos —
   todo o resto exige token, incluindo esses cadastros que, a rigor,
   soam como fluxos de "auto-cadastro". Se isso não for o comportamento
   desejado para o seu produto, ajuste `routes/api.php`.

8. **Código de status HTTP das criações.** Mantido 1:1 com o
   `ResponseEntity` da API original: `201` para
   oficinas/serviços/motoristas/veículos, `200` para
   agendamentos/avaliações/garagem-virtual/chat (mesmo sendo criações) —
   o Laravel tende a inferir `201` automaticamente quando o recurso
   acabou de ser criado (`Model::$wasRecentlyCreated`), então os
   controllers desses últimos forçam `200` explicitamente.
