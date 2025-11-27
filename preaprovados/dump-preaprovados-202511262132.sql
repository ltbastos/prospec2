-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: preaprovados
-- ------------------------------------------------------
-- Server version	5.5.5-10.1.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `d_empresas`
--

DROP TABLE IF EXISTS `d_empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `d_empresas` (
  `cnpj` char(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `razao_social` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `porte` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_cnae` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnae` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rua` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DDD` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cep` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cd_cnae` int(11) DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cnpj`),
  KEY `idx_empresas_estado_cidade` (`estado`,`cidade`),
  KEY `idx_empresas_razao` (`razao_social`(191)),
  KEY `idx_empresas_uf_cidade_bairro` (`estado`,`cidade`,`bairro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `d_empresas`
--

LOCK TABLES `d_empresas` WRITE;
/*!40000 ALTER TABLE `d_empresas` DISABLE KEYS */;
INSERT INTO `d_empresas` VALUES ('11111111000101','Alpha Tecnologia LTDA','ME','SP','São Paulo','Butantã','6201500','Desenvolvimento de programas de computador sob encomenda','Rua Fictícia Um','100',NULL,'11','30000000','11990000001','-23.565000','-46.730000','2025-11-26 21:31:38','2025-11-26 21:31:38','05500-000',6201500,'contato@alphatec.com.br'),('22222222000102','Beta Comércio de Alimentos EPP','EPP','SP','Osasco','Centro','4711301','Comércio varejista de produtos alimentícios','Av. Central','250','Sala 12','11','35000000','11990000002','-23.533000','-46.792000','2025-11-26 21:31:38','2025-11-26 21:31:38','06000-000',4711301,'vendas@betaalimentos.com.br'),('33333333000103','Gama Serviços Financeiros SA','GRANDE','RJ','Rio de Janeiro','Centro','6422100','Correspondentes de instituições financeiras','Rua da Bolsa','50',NULL,'21','40000000','21990000003','-22.903500','-43.177000','2025-11-26 21:31:38','2025-11-26 21:31:38','20010-000',6422100,'contato@gamafin.com.br'),('44444444000104','Delta Logística ME','ME','MG','Belo Horizonte','Savassi','4930201','Transporte rodoviário de carga','Rua das Entregas','800','Galpão A','31','32000000','31990000004','-19.933000','-43.933000','2025-11-26 21:31:38','2025-11-26 21:31:38','30140-000',4930201,'contato@deltalog.com.br'),('55555555000105','Épsilon Saúde Integrada LTDA','MÉDIA','PR','Curitiba','Batel','8630501','Atividades de atenção ambulatorial','Alameda Saúde','45','Conj. 701','41','33000000','41990000005','-25.441000','-49.276000','2025-11-26 21:31:38','2025-11-26 21:31:38','80420-000',8630501,'contato@epsilonsaude.com.br');
/*!40000 ALTER TABLE `d_empresas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `d_produtos`
--

DROP TABLE IF EXISTS `d_produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `d_produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordem` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `d_produtos`
--

LOCK TABLES `d_produtos` WRITE;
/*!40000 ALTER TABLE `d_produtos` DISABLE KEYS */;
INSERT INTO `d_produtos` VALUES (1,'Capital de Giro PJ',1),(2,'Conta Garantida',2),(3,'Cartão Empresarial',3),(4,'Limite Cheque Empresa',4),(5,'Antecipação de Recebíveis',5);
/*!40000 ALTER TABLE `d_produtos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `f_preaprovados`
--

DROP TABLE IF EXISTS `f_preaprovados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `f_preaprovados` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cnpj` char(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_referencia` date NOT NULL,
  `id_produto` int(11) NOT NULL,
  `valor_pre_aprovado` decimal(18,2) NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `DATA_HOJE` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `produto` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_f_pre_empresa_produto_data` (`cnpj`,`id_produto`,`data_referencia`),
  KEY `idx_f_pre_cnpj_data` (`cnpj`,`data_referencia`),
  KEY `idx_f_pre_produto` (`id_produto`),
  KEY `idx_f_preaprovados_cnpj_produto` (`cnpj`,`id_produto`),
  KEY `idx_f_preaprovados_produto_valor` (`id_produto`,`valor_pre_aprovado`),
  KEY `idx_f_preaprovados_produtos` (`cnpj`),
  CONSTRAINT `fk_f_pre_empresa` FOREIGN KEY (`cnpj`) REFERENCES `d_empresas` (`cnpj`),
  CONSTRAINT `fk_f_pre_produto` FOREIGN KEY (`id_produto`) REFERENCES `d_produtos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7725613 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `f_preaprovados`
--

LOCK TABLES `f_preaprovados` WRITE;
/*!40000 ALTER TABLE `f_preaprovados` DISABLE KEYS */;
INSERT INTO `f_preaprovados` VALUES (7725603,'11111111000101','2024-10-01',1,150000.00,'2025-11-26 21:31:38','2025-11-26 21:31:38','2024-10-15','Capital de Giro PJ'),(7725604,'11111111000101','2024-10-01',3,30000.00,'2025-11-26 21:31:38','2025-11-26 21:31:38','2024-10-15','Cartão Empresarial'),(7725605,'11111111000101','2024-11-01',5,80000.00,'2025-11-26 21:31:38','2025-11-26 21:31:38','2024-11-10','Antecipação de Recebíveis'),(7725606,'22222222000102','2024-10-01',1,120000.00,'2025-11-26 21:31:38','2025-11-26 21:31:38','2024-10-16','Capital de Giro PJ'),(7725607,'22222222000102','2024-10-01',2,50000.00,'2025-11-26 21:31:38','2025-11-26 21:31:38','2024-10-16','Conta Garantida'),(7725608,'22222222000102','2024-11-01',5,60000.00,'2025-11-26 21:31:38','2025-11-26 21:31:38','2024-11-12','Antecipação de Recebíveis'),(7725609,'33333333000103','2024-10-01',3,100000.00,'2025-11-26 21:31:38','2025-11-26 21:31:38','2024-10-20','Cartão Empresarial'),(7725610,'33333333000103','2024-11-01',4,200000.00,'2025-11-26 21:31:38','2025-11-26 21:31:38','2024-11-05','Limite Cheque Empresa'),(7725611,'44444444000104','2024-10-01',1,90000.00,'2025-11-26 21:31:38','2025-11-26 21:31:38','2024-10-18','Capital de Giro PJ'),(7725612,'55555555000105','2024-10-01',2,110000.00,'2025-11-26 21:31:38','2025-11-26 21:31:38','2024-10-22','Conta Garantida');
/*!40000 ALTER TABLE `f_preaprovados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'preaprovados'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-26 21:32:40
