-- Baratex: cria 1 motorista (com veículo) e 1 prestador (oficina + serviços).
-- Rode no pgAdmin (Query Tool) conectado no banco baratex_db.
-- Pode rodar mais de uma vez: não duplica nada.
--
-- Logins (senha dos dois: Baratex@123)
--   motorista: motorista.teste@baratex.com
--   prestador: oficina.teste@baratex.com

CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- ===== MOTORISTA =====
INSERT INTO usuarios (id, nome, email, senha_hash, telefone, tipo_usuario)
VALUES (
  'a0000000-0000-4000-8000-000000000001',
  'João Motorista',
  'motorista.teste@baratex.com',
  replace(crypt('Baratex@123', gen_salt('bf', 10)), '$2a$', '$2y$'),  -- formato bcrypt do Laravel
  '11988887777',
  'MOTORISTA'
) ON CONFLICT (email) DO NOTHING;

INSERT INTO motoristas (id, cpf, usuario_id)
SELECT 'a0000000-0000-4000-8000-000000000002', '52998224725', u.id
FROM usuarios u WHERE u.email = 'motorista.teste@baratex.com'
ON CONFLICT DO NOTHING;

-- Veículo (usa o primeiro modelo cadastrado pelo seeder de marcas/modelos)
INSERT INTO tb_veiculo (id, placa, ano, cor, modelo_id, motorista_id)
SELECT 'a0000000-0000-4000-8000-000000000003', 'ABC1D23', 2020, 'Prata',
       (SELECT id FROM modelos_veiculos ORDER BY nome_modelo LIMIT 1),
       m.id
FROM motoristas m
JOIN usuarios u ON u.id = m.usuario_id
WHERE u.email = 'motorista.teste@baratex.com'
  AND EXISTS (SELECT 1 FROM modelos_veiculos)
ON CONFLICT DO NOTHING;

-- ===== PRESTADOR (OFICINA) =====
INSERT INTO usuarios (id, nome, email, senha_hash, telefone, tipo_usuario)
VALUES (
  'b0000000-0000-4000-8000-000000000001',
  'Oficina Teste',
  'oficina.teste@baratex.com',
  replace(crypt('Baratex@123', gen_salt('bf', 10)), '$2a$', '$2y$'),
  '1133334444',
  'OFICINA'
) ON CONFLICT (email) DO NOTHING;

INSERT INTO enderecos (id, logradouro, numero, bairro, cidade, estado, cep, latitude, longitude)
VALUES (
  'b0000000-0000-4000-8000-000000000002',
  'Avenida Paulista', '1000', 'Bela Vista', 'São Paulo', 'SP', '01310100',
  -23.5614, -46.6559
) ON CONFLICT DO NOTHING;

INSERT INTO oficinas (id, razao_social, nome_fantasia, cnpj, usuario_id, endereco_id)
SELECT 'b0000000-0000-4000-8000-000000000003',
       'Oficina Teste Mecânica LTDA', 'Oficina Teste', '11222333000181',
       u.id, 'b0000000-0000-4000-8000-000000000002'
FROM usuarios u WHERE u.email = 'oficina.teste@baratex.com'
ON CONFLICT DO NOTHING;

INSERT INTO servicos (id, oficina_id, nome, descricao, preco_base, tempo_estimado_minutos) VALUES
  ('b0000000-0000-4000-8000-000000000011', 'b0000000-0000-4000-8000-000000000003',
   'Troca de Óleo e Filtro', 'Óleo sintético 5W30 e filtro de óleo.', 220.00, 40),
  ('b0000000-0000-4000-8000-000000000012', 'b0000000-0000-4000-8000-000000000003',
   'Alinhamento e Balanceamento', 'Alinhamento 3D e balanceamento das 4 rodas.', 150.00, 50),
  ('b0000000-0000-4000-8000-000000000013', 'b0000000-0000-4000-8000-000000000003',
   'Revisão de Freios', 'Pastilhas, discos e fluido de freio.', 180.00, 60)
ON CONFLICT DO NOTHING;

-- ===== CONFERÊNCIA =====
SELECT u.email, u.tipo_usuario,
       (SELECT count(*) FROM tb_veiculo v JOIN motoristas m ON m.id = v.motorista_id WHERE m.usuario_id = u.id) AS veiculos,
       (SELECT count(*) FROM servicos s JOIN oficinas o ON o.id = s.oficina_id WHERE o.usuario_id = u.id) AS servicos
FROM usuarios u
WHERE u.email IN ('motorista.teste@baratex.com', 'oficina.teste@baratex.com');
