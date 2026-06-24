# willkerms/nfs-e

Biblioteca PHP para emissão e gestão de **NFS-e (Nota Fiscal de Serviço Eletrônica)** no Brasil.

Cobre a assinatura digital dos XMLs (XML-DSig), a comunicação com os WebServices das prefeituras (SOAP ou cURL) e o parsing dos retornos. O destaque é a classe **`NFSeGenerico`**, um motor **dirigido por templates** capaz de falar com diversas prefeituras (padrões ABRASF e Nacional/DPS) **sem alterar o código-fonte** — basta apontar/editar um conjunto de templates XML e ajustar a configuração.

---

## Índice

- [Requisitos e instalação](#requisitos-e-instalação)
- [Conceitos](#conceitos)
- [Estrutura do projeto](#estrutura-do-projeto)
- [A classe base `NFSe`](#a-classe-base-nfse)
- [`NFSeGenerico` — o motor genérico](#nfsegenerico--o-motor-genérico)
  - [Por que um motor por templates](#por-que-um-motor-por-templates)
  - [Construtor e configuração (`$aConfig`)](#construtor-e-configuração-aconfig)
  - [Certificado digital](#certificado-digital)
  - [O mini-engine de templates](#o-mini-engine-de-templates)
  - [Pacotes de templates (prefeituras)](#pacotes-de-templates-prefeituras)
  - [Operações disponíveis](#operações-disponíveis)
  - [Modelo de objetos de entrada](#modelo-de-objetos-de-entrada)
  - [Formato do retorno](#formato-do-retorno)
  - [Como adicionar uma nova prefeitura](#como-adicionar-uma-nova-prefeitura)
- [Implementações legadas (Ginfes, ISSWeb, Sigep)](#implementações-legadas-ginfes-issweb-sigep)
- [Exemplos completos](#exemplos-completos)
- [Licença e autoria](#licença-e-autoria)

---

## Requisitos e instalação

| Requisito | Versão |
|-----------|--------|
| PHP | `>= 5.5` (recomenda-se 7.4+; o padrão Nacional/DPS usa _union types_, que exigem **PHP 8.0+**) |
| Extensões | `openssl`, `soap`, `curl`, `dom`, `libxml`, `mbstring`, `iconv` |
| Dependência | [`willkerms/pqd`](https://packagist.org/packages/willkerms/pqd) (utilitários, inclui o engine de templates) |

```bash
composer require willkerms/nfs-e
```

Autoload PSR-4 (`NFSe\`) + classmap das pastas `generico/`, `sigep/`, `issweb/`, `ginfes/` (configurado no `composer.json`).

> A biblioteca usa a constante global `IS_DEVELOPMENT`. Quando `true`, o `SoapClient` é criado com `trace`/`exceptions` ativos e sem cache de WSDL. Defina-a no _bootstrap_ da sua aplicação (`define('IS_DEVELOPMENT', false);`) antes de instanciar as classes.

---

## Conceitos

A NFS-e é um documento municipal: **cada prefeitura** publica seu próprio WebService, layout de XML e regras. Historicamente, a maioria seguia o padrão **ABRASF** (com pequenas variações por cidade); a partir da reforma tributária surge o **padrão Nacional**, baseado no **DPS** (_Declaração de Prestação de Serviços_) e com os tributos **IBS/CBS**.

Esta biblioteca aborda os dois cenários:

- **Modelo por templates (`NFSeGenerico`)** — abstrai a montagem do XML e a comunicação. Indicado para integrar novas prefeituras rapidamente: você descreve o XML em um template e configura o transporte.
- **Implementações específicas legadas** (`ginfes/`, `issweb/`, `sigep/`) — classes "à mão" para padrões/cidades específicas, anteriores ao motor genérico.

Independentemente do caminho, o fluxo é sempre:

```
monta XML  →  assina (XML-DSig)  →  embrulha no envelope SOAP (se aplicável)  →  transmite  →  faz parsing do retorno
```

---

## Estrutura do projeto

```
nfs-e/
├── NFSe.php              # Classe base: certificado, assinatura XML-DSig, SOAP/cURL
├── NFSeDocument.php      # DOMDocument com helper getValue()
├── NFSeElement.php       # DOMElement (registro de nó)
├── NFSeReturn.php        # Base de parsing de retorno (detecção de SOAP Fault)
├── generico/             # >>> Motor genérico (NFSeGenerico) + DTOs <<<
│   ├── NFSeGenerico.php          # Motor principal dirigido por templates
│   ├── NFSeGenericoReturn.php    # Parser de retorno do motor genérico
│   ├── NFSeGenericoInfRps.php    # DTO de entrada (RPS / padrão ABRASF)
│   ├── NFSeGenerico*.php         # Demais DTOs ABRASF (Servico, Valores, Tomador, ...)
│   └── nfseNacional/             # DTOs do padrão Nacional (DPS, IBS/CBS, ...)
├── templates/            # Pacotes de templates XML, uma pasta por prefeitura/padrão
│   ├── abrasf-v2.4/
│   ├── prefGoiania-v1/
│   ├── notacontrol-br-v1/
│   └── ...
├── ginfes/  issweb/  sigep/      # Implementações específicas legadas
├── schemas/              # XSDs de referência
├── docs/                 # Documentação de apoio (modelo conceitual ABRASF, etc.)
├── xml/                  # Saída padrão dos XMLs gerados/retornados (pathSaveXMLs)
└── testes/               # Exemplos executáveis
```

---

## A classe base `NFSe`

`NFSe\NFSe` concentra a infraestrutura comum. Você raramente a usa diretamente — `NFSeGenerico` (e as implementações legadas) herdam dela.

```php
public function __construct($certPrivKey, $certPubKey, $certKey)
```

| Parâmetro | Descrição |
|-----------|-----------|
| `$certPrivKey` | Caminho do arquivo PEM da **chave privada** |
| `$certPubKey`  | Caminho do arquivo PEM do **certificado público** |
| `$certKey`     | Caminho do arquivo PEM combinado (privada + pública), usado pelo SOAP/cURL como `local_cert` |

Principais métodos:

| Método | O que faz |
|--------|-----------|
| `loadPfx($pfxContent, $password, $createFiles, $ignoreValidity, $pathFiles, $nameFiles)` | Lê um `.pfx`/`.p12`, valida a validade do certificado e (opcionalmente) gera os PEMs (`*_priKEY.pem`, `*_pubKEY.pem`, `*_certKEY.pem`). |
| `signXML($docxml, $tagid, $appendTag, $ns, $firstChild, $createNS, $aSerach, $aReplace)` | Assina o XML no padrão **XML-DSig** (SHA-1/RSA, C14N). `$tagid` = tag assinada; `$appendTag` = tag onde a `<Signature>` é pendurada; `$ns` = prefixo de namespace. |
| `getSoap($wsdl, $options)` / `soap($wsdl, $url, $action, $data, $version)` | Cria/usa um `SoapClient` e faz `__doRequest`. |
| `curl($url, $data, $parametros, $port, $proxy)` | POST HTTP via cURL (porta 443 ⇒ autenticação SSL com certificado de cliente). |
| `setSslProtocol($n)` | Força a versão de SSL/TLS no cURL (0 = automático). |

A assinatura e o tratamento de certificado são derivados do projeto [nfephp-org](https://github.com/nfephp-org).

---

## `NFSeGenerico` — o motor genérico

`NFSe\generico\NFSeGenerico` é o coração da biblioteca.

### Por que um motor por templates

Em vez de escrever uma classe nova para cada prefeitura, o `NFSeGenerico`:

1. **Monta o XML a partir de um template** (`templates/<pacote>/<arquivo>.xml`), preenchendo placeholders e resolvendo blocos condicionais com os dados do objeto que você passou;
2. **Assina** as tags configuradas;
3. **Embrulha** no envelope SOAP (template `Soap.xml`), quando aplicável;
4. **Transmite** por SOAP ou cURL conforme a configuração;
5. **Faz o parsing** do retorno em uma estrutura de _arrays_/objetos previsível (via `NFSeGenericoReturn`).

Para integrar uma nova prefeitura, no caso ideal **basta criar/ajustar os templates e a configuração** — sem tocar na classe.

### Construtor e configuração (`$aConfig`)

```php
public function __construct(array $aConfig, $isHomologacao = true)
```

- **`$aConfig`** — _array_ de configuração (detalhado abaixo). Valores ausentes recebem _defaults_ via `PQDUtil::setDefault`, então você só precisa informar o que difere do padrão.
- **`$isHomologacao`** — `true` usa o ambiente `homologacao`; `false` usa `producao`. O construtor lança exceção se o `wsdl` do ambiente escolhido não estiver configurado.

> Cada chave do _array_ `$aConfig` está **comentada linha a linha no próprio código-fonte**, dentro do `__construct`. A tabela abaixo é o resumo de referência.

#### Chaves de topo

| Chave | Default | Descrição |
|-------|---------|-----------|
| `cpfCnpj` | `''` | CPF (11) ou CNPJ (14) do prestador/emissor. Preenche `{@CpfPrestador}`/`{@CnpjPrestador}` e decide, pelo tamanho, entre os blocos `{@ifCpf...}`/`{@ifCnpj...}`. _Compatibilidade:_ aceita também `cnpj`. |
| `insMunicipal` | `''` | Inscrição Municipal do prestador. Preenche `{@InscricaoMunicipal}` e ativa `{@ifInscricaoMunicipal}`. _Compatibilidade:_ aceita também `inscMunicipal`. |
| `escapeAsHTML` | `false` | `true` escapa o texto dos objetos com entidades HTML nomeadas; `false` usa `htmlspecialchars`. |
| `retirarAcentos` | `false` | Remove acentos do texto. **Só funciona se `escapeAsHTML = true`.** |
| `hasConsultaUrlNfse` | `false` | Indica se a prefeitura oferece o serviço de consulta da URL pública da NFS-e. |
| `pathSaveXMLs` | `./xml/` | Diretório onde os XMLs enviados e os retornos são gravados (arquivo/depuração). Se não existir, nada é salvo. |
| `verAplic` | `'1.01'` | (padrão Nacional) Versão do aplicativo emissor, usada no cancelamento por evento. |

#### `curl` — transporte cURL (quando `typeCommunication = 'curl'`)

| Chave | Default | Descrição |
|-------|---------|-----------|
| `curl.header` | `['Content-Type: text/xml']` | Cabeçalhos HTTP (concatenados com o `header` do método). |
| `curl.port` | `443` | Porta. **443 ⇒ autenticação SSL** com certificado de cliente. |

#### `soap` — transporte SOAP (default)

| Chave | Default | Descrição |
|-------|---------|-----------|
| `soap.version` | `'1.1'` | Versão do protocolo SOAP: `'1.1'` ou `'1.2'`. |

#### `tagMensagensRetorno` — tags de mensagens de retorno

| Chave | Default | Descrição |
|-------|---------|-----------|
| `tagListaMensagens` | `'ListaMensagemRetorno'` | Tag que envolve a lista de mensagens no XML de resposta. |
| `tagMensagem` | `'MensagemRetorno'` | Tag de cada mensagem (com `Codigo`/`Mensagem`/`Correcao`). |

#### `autenticacao` — credenciais

| Chave | Default | Descrição |
|-------|---------|-----------|
| `autenticacao.type` | `'none'` | Onde injetar as credenciais: `'none'` (não injeta), `'xml'` (no corpo XML), `'soap'` (no envelope SOAP). |
| `autenticacao.tagUsuario` | `'username'` | Nome do placeholder do usuário → `{@username}` (e o `if` `{@ifUsername}`). |
| `autenticacao.tagPassword` | `'password'` | Nome do placeholder da senha → `{@password}`. |
| `autenticacao.tagChavePrivada` | `'chavePrivada'` | Nome do placeholder da chave/token → `{@chavePrivada}`. |

#### `homologacao` / `producao` — ambientes

Mesmas chaves para cada ambiente (`homologacao` quando `$isHomologacao = true`, `producao` caso contrário):

| Chave | Default | Descrição |
|-------|---------|-----------|
| `wsdl` | `''` | URL do WSDL/endpoint (**obrigatório** no ambiente escolhido). |
| `usuario` / `senha` / `chavePrivada` | `''` | Credenciais injetadas quando `autenticacao.type` é `'xml'` ou `'soap'`. |
| `url` _(opcional)_ | = `wsdl` | Endpoint POST distinto do WSDL, quando necessário. |
| `urlNfse` _(opcional)_ | `''` | Template da URL pública da NFS-e. Placeholders disponíveis: `{@cpfCnpj}`, `{@inscMunicipal}`, `{@numeroNFSe}`, `{@nDFSe}`, `{@codigoVerificacao}`, `{@sha1CodigoVerificacao}`, `{@idInfNfse}`. |

#### `templates` — arquivos de template por operação

| Chave | Default | Descrição |
|-------|---------|-----------|
| `templates.path` | `./templates/` | Diretório raiz dos templates. |
| `templates.folder` | `'abrasf-v2.4'` | **Pacote** = pasta da prefeitura/padrão. Trocar a pasta = trocar de prefeitura sem mexer no código. |
| `templates.rps` | `'Rps.xml'` | RPS (ABRASF). |
| `templates.dps` | `'DPS.xml'` | DPS (Nacional). |
| `templates.enviarLoteRps` | `'EnviarLoteRps.xml'` | Envelope de lote de RPS. |
| `templates.deducao` | `'Deducao.xml'` | Parcial de uma dedução (repetido por item). |
| `templates.gerarNfse` | `'GerarNfseEnvio.xml'` | Envelope de geração (recebe o RPS/DPS assinado em `{@Rps}`/`{@DPS}`). |
| `templates.consultarNFSePorRps` | `'ConsultarNfseRpsEnvio.xml'` | Consulta por RPS. |
| `templates.consultarNFSePorDps` | `'ConsultarNfseDpsEnvio.xml'` | Consulta por DPS. |
| `templates.consultarLoteRps` | `'ConsultarLoteRpsEnvio.xml'` | Consulta de lote por protocolo. |
| `templates.consultarUrlNfse` | `'ConsultarUrlNfseEnvio.xml'` | Consulta da URL pública. |
| `templates.cancelarNfse` | `'CancelarNfseEnvio.xml'` | Cancelamento. |
| `templates.soap` | `'Soap.xml'` | Envelope SOAP (`{@xml}`, `{@action}`). |

> Os métodos do **padrão Nacional** esperam chaves adicionais nesta lista, resolvidas no mesmo pacote: `cancelarNFSeEnvio`, `consultarLoteDpsEnvio`, `consultarNfseDpsEnvio`, `enviarLoteDpsEnvio`, além dos parciais usados na montagem do DPS — `documentoDocDedRed`, `itemPed`, `refNfse`, `documentoReeRepRes`.

#### `metodos` — configuração por operação

Cada operação tem um sub-array. **Chaves comuns:**

| Chave | Descrição |
|-------|-----------|
| `action` | Nome da operação / `SOAPAction` chamada no WebService. |
| `typeCommunication` | `'soap'` (default) ou `'curl'`. |
| `actionSoapHeader` | `SOAPAction` enviado no cabeçalho, quando difere de `action`. |
| `nameSpace` | Prefixo de namespace usado na tag `<Signature>` (ex.: `'ds:'`; vazio = sem prefixo). |
| `tagSign` | Tag cujo conteúdo é **assinado** digitalmente. |
| `tagAppend` | Tag sob a qual a `<Signature>` gerada é **anexada**. |
| `tagMap.return` | Tag externa da resposta de onde o XML de retorno é extraído. |
| `tagMap.*` | Tags adicionais por operação: `tagResposta`, `respostaLote`, `respostaConsultaLote`. |
| `signConsulta` / `signRps` / `signDps` | Liga/desliga a assinatura nas consultas, nos RPS de lote ou nos DPS de lote. |
| `returnType` | `'child'` (default) ou `'string'` (quando o payload vem como texto/CDATA e precisa ser reparseado). |
| `returnReplace` | `['search'=>…, 'replace'=>…]` aplicado ao texto antes de reparsear (usado com `returnType = 'string'`). |
| `replaceXmlSOAP` | Substituições extras no envelope SOAP (prefeituras com mais de um `action`). |
| `search` / `replace` | Strings normalizadas no XML **antes de assinar** (evita invalidar a assinatura). |
| `allowCancel` | (cancelamento) `false` ⇒ não transmite e devolve um retorno simulado de "cancelado". |
| `codCancelamento` | (cancelamento) Código padrão (`1` = Erro na emissão). |

Defaults relevantes por operação:

| Operação | `action` | `tagSign` | `tagAppend` |
|----------|----------|-----------|-------------|
| `gerarNfse` | `gerarNfse` | `InfDeclaracaoPrestacaoServico` | `Rps` |
| `enviarLoteRps` | `RecepcionarLoteRps` | `LoteRps` | `EnviarLoteRpsEnvio` |
| `consultarNFSePorRps` | `consultarNfseRps` | `Pedido` | `ConsultarNfseRpsEnvio` |
| `consultarNFSePorDps` | `consultarNfseDps` | `Pedido` | `ConsultarNfseDpsEnvio` |
| `consultarLoteRps` | `ConsultarLoteRps` | — | — |
| `consultarUrlNfse` | `ConsultarUrlNfse` | `Pedido` | `ConsultarUrlNfseEnvio` |
| `cancelarNfse` | `cancelarNfse` | `InfPedidoCancelamento` | `Pedido` |

#### `fields` — transformações de campo (opcional)

Funções aplicadas a um campo antes de ele ir para o template (`applyFnField`). A chave é o nome do campo em _camelCase_ (o placeholder `{@DataEmissao}` corresponde a `dataEmissao`):

```php
'fields' => [
    'dataEmissao' => [
        'fn'   => 'substr',     // função PHP chamada sobre o valor
        'args' => [0, 10],      // argumentos extras (o valor do campo é sempre o 1º)
    ],
],
```

### Certificado digital

Há dois jeitos de informar o certificado no `$aConfig`:

**1. Direto, a partir de um `.pfx`/`.p12`** — a biblioteca gera os PEMs temporários e os apaga no destrutor:

```php
$aConfig['pfx']    = '/caminho/certificado.pfx';  // conteúdo carregado com file_get_contents internamente
$aConfig['pwdPFX'] = 'senha-do-certificado';
$aConfig['cnpj']   = '00000000000000';            // usado como prefixo do nome dos PEMs temporários
```

**2. Apontando PEMs já existentes:**

```php
$aConfig['privKey'] = '/caminho/priKEY.pem';
$aConfig['pubKey']  = '/caminho/pubKEY.pem';
$aConfig['certKey'] = '/caminho/certKEY.pem';
```

### O mini-engine de templates

Os templates são XML com dois tipos de marcação, resolvidos por `PQDUtil::procTplText`:

| Marcação | Significado |
|----------|-------------|
| `{@variavel}` | Substituída pelo valor correspondente. |
| `{@ifAlgo} … {@endifAlgo}` | Bloco mantido se a condição for verdadeira; removido caso contrário. |
| `{@ifAlgo} … {@elseAlgo} … {@endifAlgo}` | Forma com _else_. |

As **variáveis** e as **condições** (`bool`) são preenchidas pela `NFSeGenerico` a partir dos objetos de entrada (ex.: decidir CPF × CNPJ pelo comprimento, incluir um bloco só quando o campo opcional existe, etc.). Quem escreve o template apenas posiciona os placeholders.

Exemplo (trecho de `Rps.xml`):

```xml
<Servico>
    <Valores>
        <ValorServicos>{@ValorServicos}</ValorServicos>
        {@ifValorDeducoes}<ValorDeducoes>{@ValorDeducoes}</ValorDeducoes>{@endifValorDeducoes}
        {@ifAliquota}<Aliquota>{@Aliquota}</Aliquota>{@endifAliquota}
    </Valores>
    ...
</Servico>
```

Envelope SOAP (`Soap.xml`) — recebe o XML já montado/assinado em `{@xml}` e a operação em `{@action}`:

```xml
<soapenv:Envelope ...>
  <soapenv:Body>
    <nfse:{@action}>
      <nfseDadosMsg>{@xml}</nfseDadosMsg>
    </nfse:{@action}>
  </soapenv:Body>
</soapenv:Envelope>
```

### Pacotes de templates (prefeituras)

Cada pasta em `templates/` é um pacote. Selecione com `templates.folder`. Já acompanham a biblioteca:

| Pacote | Padrão / observação |
|--------|---------------------|
| `abrasf-v2.4` | Referência ABRASF 2.04 (RPS). |
| `coplan-v1`, `coplan-br-v1` | Provedor Coplan (ABRASF e DPS). |
| `fiorilli-ro-v2-01`, `fiorilli-br-v1` | Provedor Fiorilli (ABRASF e DPS). |
| `notacontrol-go-v1`, `notacontrol-br-v1` | Provedor NotaControl (ABRASF e DPS). |
| `megasoft-go-v1` | Provedor Megasoft. |
| `prefGoiania-v1` | Prefeitura de Goiânia. |
| `saatri-ba-v2-01` | Provedor Saatri. |

> Pacotes com sufixo `-br` contêm os templates do **padrão Nacional (DPS)**; os demais são variações **ABRASF (RPS)**.

### Operações disponíveis

**Padrão ABRASF (RPS):**

| Método | Parâmetro | Descrição |
|--------|-----------|-----------|
| `gerarNfse(NFSeGenericoInfRps\|NFSeGenericoInfDPS $oRps)` | RPS ou DPS | Gera uma NFS-e a partir de um RPS (ABRASF) ou DPS (Nacional). |
| `enviarLoteRps(array $aRps, NFSeGenericoLoteRps $oLote)` | Lista de RPS + lote | Envia um lote de RPS. |
| `consultarNFSePorRps(NFSeGenericoIdentificacaoRps $o)` | Identificação do RPS | Consulta a NFS-e correspondente a um RPS. |
| `consultarLoteRps(NFSeGenericoConsultarLote $o)` | Protocolo | Consulta um lote pelo protocolo. |
| `consultarUrlNfse(NFSeGenericoConsultarUrlNfse $o)` | Filtros | Consulta a URL pública / links da NFS-e. |
| `cancelarNfse(NFSeGenericoCancelarNfseEnvio $o)` | Dados do cancelamento | Cancela uma NFS-e. |

**Padrão Nacional (DPS / IBS-CBS):**

| Método | Parâmetro | Descrição |
|--------|-----------|-----------|
| `gerarNfseEnvio(NFSeGenericoInfDPS $oDps)` | DPS | Atalho para `gerarNfse()` com um DPS. |
| `enviarLoteDpsEnvio(array $aDps, $numeroLote)` | Lista de DPS | Envia um lote de DPS. |
| `consultarNfseDpsEnvio($numDPS, $serieDPS, $protocolo = null)` | Nº/série do DPS | Consulta NFS-e por DPS. |
| `consultarNFSePorDps(NFSeGenericoConsultarNfseDps $o)` | Identificação do DPS | Consulta NFS-e por DPS (objeto). |
| `consultarLoteDpsEnvio($protocolo)` | Protocolo | Consulta lote de DPS pelo protocolo. |
| `cancelarNFSeEnvio($chNFSe, $nPedRegEvento, $tpEvento = '101101', $xMotivo = null)` | Chave + evento | Cancela NFS-e por evento. |

**Utilitários:**

| Método | Descrição |
|--------|-----------|
| `getConfig($key = null, $default = null)` | Lê a configuração efetiva (toda, ou uma chave específica). |
| `getIsHomologacao()` | Indica o ambiente atual. |

Todas as operações retornam o _array_ produzido por `NFSeGenericoReturn` (ver abaixo).

### Modelo de objetos de entrada

**RPS (ABRASF)** — `NFSeGenericoInfRps` agrega os DTOs do serviço:

```
NFSeGenericoInfRps
├── IdentificacaoRps        (NFSeGenericoIdentificacaoRps: Numero, Serie, Tipo)
├── DataEmissao, Competencia, Status, NaturezaOperacao, OptanteSimplesNacional, ...
├── RpsSubstituido          (NFSeGenericoIdentificacaoRps)
├── Servico                 (NFSeGenericoServico)
│   ├── Valores             (NFSeGenericoValores: ValorServicos, Aliquota, ValorIss, ...)
│   ├── ItemListaServico, CodigoCnae, Discriminacao, CodigoMunicipio, IssRetido, ExigibilidadeISS, ...
├── Prestador               (NFSeGenericoPrestador: CpfCnpj, InscricaoMunicipal)
├── Tomador                 (NFSeGenericoTomador)
│   ├── IdentificacaoTomador (CpfCnpj, InscricaoMunicipal)
│   ├── RazaoSocial, Endereco (NFSeGenericoEndereco), Contato (NFSeGenericoContato)
├── IntermediarioServico    (NFSeGenericoIntermediarioServico)
├── ConstrucaoCivil         (NFSeGenericoConstrucaoCivil: CodigoObra, Art)
├── Evento                  (NFSeGenericoEvento)
└── aDeducoes[]             (NFSeGenericoDeducao)
```

**DPS (Nacional)** — `NFSe\generico\nfseNacional\NFSeGenericoInfDPS` agrega os grupos do padrão nacional:

```
NFSeGenericoInfDPS
├── tpAmb, dhEmi, verAplic, serie, nDPS, dCompet, tpEmit, cLocEmi, ...
├── subst    (NFSeGenericoSubstituicao)        # NFS-e substituída
├── prest    (NFSeGenericoPrest)               # prestador (+ regTrib)
├── toma     (NFSeGenericoToma)                # tomador
├── interm   (NFSeGenericoInterm)              # intermediário
├── serv     (NFSeGenericoServ)               # serviço (locPrest, cServ, comExt, obra, atvEvento, infoCompl)
├── valores  (NFSeGenericoDPSValores)         # valores, descontos, deduções, tributação municipal/federal
└── IBSCBS   (NFSeGenericoIBSCBS)             # grupo IBS/CBS (reforma tributária)
```

### Formato do retorno

As operações devolvem _arrays_ associativos. As mensagens vêm como objetos `NFSeGenericoMensagemRetorno` (`Codigo`, `Mensagem`, `Correcao`, `IdDPS`) e as notas como `NFSeGenericoInfNFSe` (`Numero`, `CodigoVerificacao`, `DataEmissao`, `Url`, `Servico`, `PrestadorServico`, `TomadorServico`, ...).

| Operação | Estrutura do retorno |
|----------|----------------------|
| `gerarNfse` | `['ListaMensagemRetorno' => [...], 'ListaNfse' => ['CompNfse' => [InfNFSe...], 'ListaMensagemAlertaRetorno' => [...]]]` |
| `enviarLoteRps` | `['NumeroLote', 'DataRecebimento', 'Protocolo', 'ListaNfse', 'ListaMensagemRetorno', 'ListaMensagemRetornoLote']` |
| `consultarNFSePorRps` | `['ListaMensagemRetorno' => [...], 'CompNfse' => InfNFSe]` |
| `consultarLoteRps` | `['Situacao', 'ListaNfse', 'ListaMensagemRetorno', 'ListaMensagemRetornoLote']` |
| `consultarUrlNfse` | `['ListaMensagemRetorno' => [...], 'ListaLinks' => [['NumeroNfse', 'CodigoVerificacao', 'Url', 'UrlAutenticidade'], ...]]` |
| `cancelarNfse` | `['ListaMensagemRetorno' => [...], 'RetCancelamento' => ['NfseCancelamento' => [...]]]` |

Regra prática: uma operação **deu certo** quando `ListaMensagemRetorno` está vazia e o bloco de dados esperado (`ListaNfse`, `CompNfse`, etc.) foi preenchido. SOAP _Faults_ e respostas fora do formato esperado também são convertidos em entradas de `ListaMensagemRetorno`.

### Como adicionar uma nova prefeitura

1. **Crie um pacote** em `templates/<provedor-uf-versao>/` (copie um pacote próximo como base).
2. **Ajuste os templates** (`Rps.xml`/`DPS.xml`, `GerarNfseEnvio.xml`, `Soap.xml`, consultas, cancelamento) para o layout exato da prefeitura — usando os placeholders `{@...}` e os blocos `{@if...}`.
3. **Monte o `$aConfig`** apontando `templates.folder` para o novo pacote, os `wsdl` de homologação/produção, o tipo de autenticação e os `metodos` (`action`, `tagSign`, `tagAppend`, `tagMap`, transporte).
4. Se a prefeitura exigir limpeza/assinatura especial, use `search`/`replace`, `signConsulta`, `returnType`/`returnReplace` e `replaceXmlSOAP`.

No melhor caso, **nenhuma linha de PHP** precisa ser escrita.

---

## Implementações legadas (Ginfes, ISSWeb, Sigep)

Antes do motor genérico, cada padrão tinha uma classe própria (com seus DTOs `*InfRps`, `*Return`, etc.):

| Namespace | Classe | Observação |
|-----------|--------|------------|
| `NFSe\ginfes` | `NFSeGinfes` | Padrão Ginfes (cabeçalho/tipos v03). |
| `NFSe\issweb` | `NFSeISSWeb` | Padrão ISSWeb (ex.: Senador Canedo). |
| `NFSe\sigep` | `NFSeSigep` | Padrão Sigep (ex.: Senador Canedo). |

Elas continuam funcionais, mas **para novas integrações prefira `NFSeGenerico`**.

---

## Exemplos completos

### Instanciando

```php
use NFSe\generico\NFSeGenerico;

$aConfig = [
    'cpfCnpj'      => '00000000000000',
    'insMunicipal' => '123456',
    'pfx'          => __DIR__ . '/cert/empresa.pfx',
    'pwdPFX'       => 'senha',
    'cnpj'         => '00000000000000',

    'templates'    => ['folder' => 'abrasf-v2.4'],

    'autenticacao' => ['type' => 'none'],

    'homologacao'  => ['wsdl' => 'https://homolog.prefeitura.gov.br/nfse?wsdl'],
    'producao'     => ['wsdl' => 'https://nfse.prefeitura.gov.br/nfse?wsdl'],
];

$oNFSe = new NFSeGenerico($aConfig, true); // true = homologação
```

### Gerando uma NFS-e (RPS / ABRASF)

```php
use NFSe\generico\NFSeGenericoInfRps;

$oRps = new NFSeGenericoInfRps();
$oRps->IdentificacaoRps->Numero = 1;
$oRps->IdentificacaoRps->Serie  = 'A';
$oRps->IdentificacaoRps->Tipo   = 1;
$oRps->DataEmissao              = date('Y-m-d');
$oRps->Competencia              = date('Y-m-d');

$oRps->Servico->Valores->ValorServicos = 100.00;
$oRps->Servico->Valores->Aliquota      = 0.05;
$oRps->Servico->ItemListaServico       = '0107';
$oRps->Servico->Discriminacao          = 'Serviços de consultoria';
$oRps->Servico->CodigoMunicipio        = '5208707';

$oRps->Prestador->CpfCnpj            = '00000000000000';
$oRps->Prestador->InscricaoMunicipal = '123456';

$oRps->Tomador->IdentificacaoTomador->CpfCnpj = '11111111111';
$oRps->Tomador->RazaoSocial                   = 'Cliente Exemplo';

$retorno = $oNFSe->gerarNfse($oRps);

if (empty($retorno['ListaMensagemRetorno'])) {
    $oNota = $retorno['ListaNfse']['CompNfse'][0];
    echo "NFS-e nº {$oNota->Numero} — {$oNota->Url}";
} else {
    foreach ($retorno['ListaMensagemRetorno'] as $msg) {
        echo "[{$msg->Codigo}] {$msg->Mensagem} — {$msg->Correcao}\n";
    }
}
```

### Gerando uma NFS-e (DPS / Nacional)

```php
use NFSe\generico\NFSeGenerico;
use NFSe\generico\nfseNacional\NFSeGenericoInfDPS;

$oNotaNacional = new NFSeGenerico($aConfigNacional); // templates.folder de um pacote "-br"

$oDPS = new NFSeGenericoInfDPS();
$oDPS->tpAmb   = 2;            // 2 = homologação
$oDPS->dhEmi   = date('c');
$oDPS->serie   = 1;
$oDPS->nDPS    = 1;
$oDPS->dCompet = date('Ymd');
$oDPS->tpEmit  = 1;
$oDPS->cLocEmi = '5208707';
// ... preencher $oDPS->prest, ->toma, ->serv, ->valores, ->IBSCBS ...

$retorno = $oNotaNacional->gerarNfse($oDPS); // ou gerarNfseEnvio($oDPS)
```

### Cancelando

```php
use NFSe\generico\NFSeGenericoCancelarNfseEnvio;

$oCancelar = new NFSeGenericoCancelarNfseEnvio();
$oCancelar->Numero                = '123';
$oCancelar->CodigoVerificacao     = 'ABC123';
$oCancelar->CodigoMunicipio       = '5208707';
$oCancelar->CodigoCancelamento    = '1'; // 1 - Erro na emissão
$oCancelar->DescricaoCancelamento = 'Erro na emissão';

$retorno = $oNFSe->cancelarNfse($oCancelar);
```

Mais exemplos executáveis em `testes/` (ex.: `TesteNotaServicoNacional.php`).

---

## Licença e autoria

- **Licença:** GPL-2.0+
- **Autor:** Willker Moraes Silva — https://github.com/willkerms
- Assinatura digital e manejo de certificados baseados no projeto [nfephp-org](https://github.com/nfephp-org).
