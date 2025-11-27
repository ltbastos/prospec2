-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: preaprovados
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
  `cnpj` char(14) NOT NULL,
  `razao_social` varchar(200) NOT NULL,
  `porte` varchar(50) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `cidade` varchar(120) DEFAULT NULL,
  `bairro` varchar(120) DEFAULT NULL,
  `cod_cnae` varchar(10) DEFAULT NULL,
  `cnae` varchar(255) DEFAULT NULL,
  `rua` varchar(200) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `complemento` varchar(120) DEFAULT NULL,
  `ddd` char(2) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`cnpj`),
  KEY `idx_empresas_estado_cidade` (`estado`,`cidade`),
  KEY `idx_empresas_razao` (`razao_social`),
  KEY `idx_empresas_uf_cidade_bairro` (`estado`,`cidade`,`bairro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `d_empresas`
--

LOCK TABLES `d_empresas` WRITE;
/*!40000 ALTER TABLE `d_empresas` DISABLE KEYS */;
INSERT INTO `d_empresas` VALUES ('11111111000191','Padaria Bom Sabor Ltda','ME','SP','São Paulo','Centro',NULL,NULL,'Rua das Flores','123','Loja 1','11','34567890','999999999',NULL,NULL,'2025-11-16 14:37:41','2025-11-16 14:58:10'),('12345678000199','Tecnologia Up Solutions S.A.','GE','SP','São Paulo','Brooklin',NULL,NULL,'Rua da Inovação','800','Torre B, 10º','11','40041234','999123456',NULL,NULL,'2025-11-16 14:37:41','2025-11-16 14:58:10'),('22222222000172','Comercial Alfa Importação Ltda','EPP','RJ','Rio de Janeiro','Copacabana',NULL,NULL,'Av. Atlântica','456','Sala 802','21','23456789','988888888',-22.97196400,-43.18254300,'2025-11-16 14:37:41','2025-11-16 18:16:17'),('33333333000153','Indústria Beta Metais S.A.','EM','MG','Belo Horizonte','Industrial',NULL,NULL,'Rua dos Operários','789',NULL,'31','33334444','979797979',NULL,NULL,'2025-11-16 14:37:41','2025-11-16 14:58:10'),('44444444000134','Supermercado Econômico EIRELI','ME','RS','Porto Alegre','Centro',NULL,NULL,'Av. Central','1000',NULL,'51','40001234','989898989',NULL,NULL,'2025-11-16 14:37:41','2025-11-16 14:58:10'),('55555555000115','Clínica Saúde Total Ltda','EPP','PR','Curitiba','Batel',NULL,NULL,'Rua das Acácias','55','Conj. 305','41','30112233','999888777',NULL,NULL,'2025-11-16 14:37:41','2025-11-16 14:58:10'),('66666666000106','Transportadora Rápido Sul Ltda','EPP','SC','Florianópolis','Trindade',NULL,NULL,'Rod. Litorânea','500','Galpão A','48','31234567','988776655',NULL,NULL,'2025-11-16 14:37:41','2025-11-16 14:58:10'),('77777777000187','Restaurante Sabor Caseiro Ltda','ME','BA','Salvador','Comércio',NULL,NULL,'Rua do Porto','210',NULL,'71','30110022','999111222',NULL,NULL,'2025-11-16 14:37:41','2025-11-16 14:58:10'),('88888888000168','Escritório Contábil Exato Ltda','ME','PE','Recife','Boa Vista',NULL,NULL,'Rua da Contabilidade','12','Sala 05','81','32223344','989900011',NULL,NULL,'2025-11-16 14:37:41','2025-11-16 14:58:10'),('99999999000149','Loja de Materiais Construcenter','EPP','GO','Goiânia','Setor Oeste',NULL,NULL,'Av. das Construções','2000',NULL,'62','36450000','987654321',NULL,NULL,'2025-11-16 14:37:41','2025-11-16 14:58:10');
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
  `nome` varchar(150) NOT NULL,
  `ordem` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `d_produtos`
--

LOCK TABLES `d_produtos` WRITE;
/*!40000 ALTER TABLE `d_produtos` DISABLE KEYS */;
INSERT INTO `d_produtos` VALUES (1,'Cagiro',1),(2,'Cartão',2),(3,'Cheque',3),(4,'Desconto',4),(5,'Lime',5),(6,'Procred',6),(7,'Pronampe',7);
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
  `cnpj` char(14) NOT NULL,
  `data_referencia` date NOT NULL,
  `id_produto` int(11) NOT NULL,
  `valor_pre_aprovado` decimal(18,2) NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_f_pre_empresa_produto_data` (`cnpj`,`id_produto`,`data_referencia`),
  KEY `idx_f_pre_cnpj_data` (`cnpj`,`data_referencia`),
  KEY `idx_f_pre_produto` (`id_produto`),
  KEY `idx_preaprovados_cnpj_produto` (`cnpj`,`id_produto`),
  KEY `idx_preaprovados_produto_valor` (`id_produto`,`valor_pre_aprovado`),
  KEY `idx_preaprovados_cnpj` (`cnpj`),
  CONSTRAINT `fk_f_pre_empresa` FOREIGN KEY (`cnpj`) REFERENCES `d_empresas` (`cnpj`),
  CONSTRAINT `fk_f_pre_produto` FOREIGN KEY (`id_produto`) REFERENCES `d_produtos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `f_preaprovados`
--

LOCK TABLES `f_preaprovados` WRITE;
/*!40000 ALTER TABLE `f_preaprovados` DISABLE KEYS */;
INSERT INTO `f_preaprovados` VALUES (1,'11111111000191','2025-01-31',1,50000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(2,'11111111000191','2025-01-31',2,15000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(3,'11111111000191','2025-01-31',4,20000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(4,'22222222000172','2025-01-31',1,120000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(5,'22222222000172','2025-01-31',3,30000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(6,'22222222000172','2025-01-31',7,80000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(7,'33333333000153','2025-01-31',1,300000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(8,'33333333000153','2025-01-31',5,150000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(9,'33333333000153','2025-01-31',6,100000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(10,'44444444000134','2025-01-31',1,200000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(11,'44444444000134','2025-01-31',2,50000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(12,'44444444000134','2025-01-31',4,75000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(13,'55555555000115','2025-01-31',1,80000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(14,'55555555000115','2025-01-31',2,30000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(15,'66666666000106','2025-01-31',1,250000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(16,'66666666000106','2025-01-31',3,60000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(17,'66666666000106','2025-01-31',5,120000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(18,'77777777000187','2025-01-31',1,40000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(19,'77777777000187','2025-01-31',2,10000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(20,'88888888000168','2025-01-31',1,30000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(21,'88888888000168','2025-01-31',2,15000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(22,'88888888000168','2025-01-31',6,50000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(23,'99999999000149','2025-01-31',1,180000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(24,'99999999000149','2025-01-31',4,90000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(25,'99999999000149','2025-01-31',7,100000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(26,'12345678000199','2025-01-31',1,500000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(27,'12345678000199','2025-01-31',2,200000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51'),(28,'12345678000199','2025-01-31',5,300000.00,'2025-11-16 14:37:51','2025-11-16 14:37:51');
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

-- Dump completed on 2025-11-16 20:54:01
