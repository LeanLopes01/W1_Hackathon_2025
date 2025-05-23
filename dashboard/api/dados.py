import mysql.connector
from datetime import datetime
from yfinance import download
import schedule
import time
import argparse

# Esse código é para fazer uma similação dos ativos que um usuário possui, é meramente uma simulação para demoonstrar a funcionalidade do site

class PatrimonioManager:
    def __init__(self, db_config):
        self.db_config = db_config

    def _get_connection(self):
        return mysql.connector.connect(**self.db_config)
    
    def _calcular_alocacoes(self, user_id):
        conn = self._get_connection()
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT qtd_aposentadoria, qtd_casamento, 
                   qtd_bolsa, qtd_crypto 
            FROM dados_patri 
            WHERE user_id = %s
        """, (user_id,))
        
        result = cursor.fetchone()
        conn.close()
        
        return {
            'aposentadoria': result['qtd_aposentadoria'] if result else 0,
            'casamento': result['qtd_casamento'] if result else 0,
            'bolsa': result['qtd_bolsa'] if result else 0,
            'crypto': result['qtd_crypto'] if result else 0
        }

    def _processar_ativo(self, ticker, valor_investido, tipo):
        try:
            hist = download(ticker, period='1y', progress=False)
            
            if hist.empty or 'Close' not in hist.columns:
                return None
                
            # Corrigir warnings usando .iloc[0] explícito
            preco_inicial = float(hist['Close'].iloc[0])  # Converter para float explicitamente
            preco_atual = float(hist['Close'].iloc[-1])   # Converter para float explicitamente
            
            rendimento = ((preco_atual - preco_inicial) / preco_inicial) * 100
            
            return {
                'ativo': ticker,  # Adicionando o ticker aos dados
                'tipo': tipo,
                'valor_investido': float(valor_investido),
                'rendimento_percentual': float(rendimento),
                'valor_atual': float(valor_investido * (1 + rendimento/100))
            }
        except Exception as e:
            print(f"Erro no ativo {ticker}: {str(e)}")
            return None

    def _salvar_snapshot(self, user_id, resultados):
        conn = self._get_connection()
        cursor = conn.cursor()
        
        # Inserir snapshot principal com a nova coluna
        cursor.execute("""
            INSERT INTO patrimony_snapshots (
                user_id, patrimonio_liquido, total_rendimentos,
                rentabilidade_total_percentual,  # Nova coluna
                max_alta_ativo, max_alta_valor, max_baixa_ativo, max_baixa_valor
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
        """, (
            user_id,
            resultados['rendimentos']['patrimonio_liquido'],
            resultados['rendimentos']['total'],
            resultados['rendimentos']['rentabilidade_total_percentual'],  # Novo valor
            resultados['rendimentos']['max_alta']['ativo'],
            resultados['rendimentos']['max_alta']['valor'],
            resultados['rendimentos']['max_baixa']['ativo'],
            resultados['rendimentos']['max_baixa']['valor']
        ))
        
        snapshot_id = cursor.lastrowid
        
        # Inserir alocações detalhadas
        for ativo, dados in resultados['alocacoes'].items():
            cursor.execute("""
                INSERT INTO patrimony_allocations (
                    snapshot_id, ativo, tipo,
                    valor_investido, rendimento_percentual, valor_atual
                ) VALUES (%s, %s, %s, %s, %s, %s)
            """, (
                snapshot_id,
                ativo,
                dados['tipo'],
                dados['valor_investido'],
                dados['rendimento_percentual'],
                dados['valor_atual']
            ))
        
        conn.commit()
        conn.close()
        return snapshot_id

    def calcular_rendimentos(self, user_id):
        try:
            print(f"Iniciando cálculo para user_id={user_id}")
            alocacoes = self._calcular_alocacoes(user_id)
            print(f"Alocações obtidas: {alocacoes}")
            resultados = {'alocacoes': {}, 'rendimentos': {}}
            
            # Processar Tesouros
            tesouros = [
                ('aposentadoria', 'IRFM11.SA', 'TESOURO'),
                ('casamento', 'B5P211.SA', 'TESOURO')
            ]
            
            for objetivo, ticker, tipo in tesouros:
                if alocacoes[objetivo] > 0:
                    dados = self._processar_ativo(ticker, alocacoes[objetivo], tipo)
                    if dados:
                        resultados['alocacoes'][ticker] = dados
            
            # Processar Bolsa
            if alocacoes['bolsa'] > 0:
                fiis = ['XPML11.SA', 'HGLG11.SA', 'KNRI11.SA', 'XPLG11.SA']
                acoes = ['PETR4.SA', 'VALE3.SA', 'ITUB4.SA', 'BBDC4.SA']
                total_ativos = len(fiis) + len(acoes)
                valor_por_ativo = alocacoes['bolsa'] / total_ativos
                
                for ticker in fiis:
                    dados = self._processar_ativo(ticker, valor_por_ativo, 'FII')
                    if dados:
                        resultados['alocacoes'][ticker] = dados
                
                for ticker in acoes:
                    dados = self._processar_ativo(ticker, valor_por_ativo, 'AÇÃO')
                    if dados:
                        resultados['alocacoes'][ticker] = dados
            
            # Processar Crypto
            if alocacoes['crypto'] > 0:
                cryptos = ['BTC-USD', 'ETH-USD']
                valor_por_moeda = alocacoes['crypto'] / len(cryptos)
                
                for ticker in cryptos:
                    dados = self._processar_ativo(ticker, valor_por_moeda, 'CRYPTO')
                    if dados:
                        resultados['alocacoes'][ticker] = dados
            
            # Calcular métricas finais
            total_atual = sum(d['valor_atual'] for d in resultados['alocacoes'].values())
            total_investido = sum(alocacoes.values())
            
            rentabilidade_total_percentual = 0.0
            if total_investido > 0:
                rentabilidade_total_percentual = ((total_atual / total_investido) - 1) * 100
            
            # Calcular máximos e mínimos
            max_alta = max(resultados['alocacoes'].values(), key=lambda x: x['rendimento_percentual'])
            max_baixa = min(resultados['alocacoes'].values(), key=lambda x: x['rendimento_percentual'])
            
            resultados['rendimentos'] = {
                'patrimonio_liquido': total_atual,
                'total': total_atual - total_investido,
                'rentabilidade_total_percentual': rentabilidade_total_percentual,  # Novo campo
                'max_alta': {
                    'ativo': max_alta['ativo'],
                    'valor': max_alta['rendimento_percentual']
                },
                'max_baixa': {
                    'ativo': max_baixa['ativo'],
                    'valor': max_baixa['rendimento_percentual']
                }
            }
            
            # Salvar no banco de dados
            self._salvar_snapshot(user_id, resultados)
            return True
        
        except Exception as e:
            print(f"Erro no cálculo: {str(e)}")
            return False

# Configuração e uso
if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument('--user-id', type=int, required=True)
    args = parser.parse_args()
    
    db_config = {
        'host': 'localhost',
        'user': 'hack',
        'password': '1234',
        'database': 'site_w1'
    }
    
    manager = PatrimonioManager(db_config)
    success = manager.calcular_rendimentos(user_id=args.user_id)
    print(f"Resultado da execução: {success}")