@extends('admin')

@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function buscarProduto(produtoId) {
        if (produtoId) {
            $.ajax({
                url: `/produtos/${produtoId}`,
                type: 'GET',
                success: function(data) {
                    $('#valor').val(data.valor);

                    $('#quantidade').removeAttr('readonly');
                    $('#valor_total').val('');
                    $('.adicionar_produto').attr('disabled', true);
                },
                error: function() {
                    alert('Erro ao buscar o produto. Tente novamente.');
                }
            });
        } else {
            $('#valor').val('');
            $('#quantidade').val('').attr('readonly', true);
            $('#valor_total').val('');
            $('.adicionar_produto').attr('disabled', true);
        }
    }

    function calcularValorTotal() {
        let valor = parseFloat($('#valor').val());
        let quantidade = parseFloat($('#quantidade').val());
        if (!isNaN(valor) && !isNaN(quantidade)) {
            let valorTotal = valor * quantidade;
            $('#valor_total').val(valorTotal.toFixed(2));
        } else {
            $('#valor_total').val('');
        }
    }
    </script>
<script>
    function submitProduto() {
        const produtoId = $('#nome_produto').val();
        const produtoNome = $('#nome_produto option:selected').text();
        const valorUnitario = parseFloat($('#valor').val());
        const quantidade = parseFloat($('#quantidade').val());
        const valorTotal = parseFloat($('#valor_total').val());

        if (produtoId && valorUnitario > 0 && quantidade > 0) {
            const novaLinha = `
                <tr>
                    <td>${produtoId}</td>
                    <td>${produtoNome}</td>
                    <td>R$ ${valorUnitario.toFixed(2)}</td>
                    <td>${quantidade}</td>
                    <td>R$ ${valorTotal.toFixed(2)}</td>
                    <td>
                        <form action="" method="post">
                            @csrf
                            @method('delete')
                            <button class="btn btn-danger btn-block rounded-0" onclick="removerProduto(this)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            `;

            $('#tabela_produtos').append(novaLinha);
            atualizarValorTotalGeral(valorTotal);

            $('#nome_produto').val('');
            $('#valor').val('');
            $('#quantidade').val('').attr('readonly', true);
            $('#valor_total').val('');
            $('.adicionar_produto').attr('disabled', true);
        }
    }

    function calcularValorTotal() {
        let valor = parseFloat($('#valor').val());
        let quantidade = parseFloat($('#quantidade').val());
        if (valor > 0 && quantidade > 0) {
            let valorTotal = valor * quantidade;
            $('#valor_total').val(valorTotal.toFixed(2));
            $('.adicionar_produto').removeAttr('disabled');
        } else {
            $('#valor_total').val('');
            $('.adicionar_produto').attr('disabled', true);
        }
    }

    function atualizarValorTotalGeral(valorTotal) {
        let valorTotalGeral = parseFloat($('#valor_total_geral').text().replace('R$ ', '').replace(',', '.')) || 0;
        valorTotalGeral += valorTotal;
        $('#valor_total_geral').text(`R$ ${valorTotalGeral.toFixed(2)}`);
    }

    function removerProduto(button) {
        $(button).closest('tr').remove();

        const valorTotalRemovido = parseFloat($(button).closest('tr').find('td:nth-child(5)').text().replace('R$ ', '').replace(',', '.'));
        atualizarValorTotalGeral(-valorTotalRemovido);
    }
</script>
<script>
 function finalizarVenda() {
    const produtos = [];

    $('#tabela_produtos tr').each(function() {
        const produtoId = $(this).find('td:nth-child(1)').text().trim();
        const quantidade = parseFloat($(this).find('td:nth-child(4)').text().trim());
        const valorUnitario = parseFloat($(this).find('td:nth-child(3)').text().replace('R$', '').trim());
        const valorTotal = parseFloat($(this).find('td:nth-child(5)').text().replace('R$', '').trim());

        if (produtoId && quantidade > 0 && valorUnitario > 0) {
            produtos.push({
                id: produtoId,
                quantidade: quantidade,
                valor_unitario: valorUnitario,
                valor_total: valorTotal
            });
        }
    });

    $('#produtos_json').val(JSON.stringify(produtos));

    if (produtos.length === 0) {
        alert('Adicione ao menos um produto para finalizar a venda.');
        return;
    }

    document.getElementById('finaliza').submit();
}
</script>
@include('mensagens.mensagem')
    <div class="row justify-content-md-center bg-light pb-3">
        <div class="col-sm-8 rounded bg-white p-3 m-1 border mt-4 p-md-4">
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>{{ session('status') }}</strong>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <div class="col-sm-12 d-flex flex-column align-items-center">
                    <form action="" method="post" id="form-add-produto" class="w-100">
                        @csrf
                        @method('post')
                        <div class="row">
                            <div class="row d-flex justify-content-center">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="nome_produto">Produto</label>
                                        <select id="nome_produto" class="form-control" name="nome_produto" onchange="buscarProduto(this.value)">
                                            <option value="">Selecione um produto</option>
                                            @foreach($produtos as $produto)
                                                <option value="{{ $produto->id }}">{{ $produto->nome }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="valor">Valor</label>
                                        <input type="number" step="0.01" name="valor" required min="0.01" value="{{ old('valor') }}" readonly id="valor" class="form-control">
                                    </div>
                                </div>

                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="quantidade">Quantidade</label>
                                        <input type="number" step="1" min="1" readonly required name="quantidade" value="" id="quantidade" class="form-control" oninput="calcularValorTotal()">
                                    </div>
                                </div>

                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="valor_total">Valor total</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" required min="0.01" name="valor_total" value="" readonly id="valor_total" class="form-control">
                                            <div class="input-group-append ml-3">
                                                <button type="button" class="pl-1 btn btn-primary adicionar_produto" onclick="submitProduto()" disabled><i class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

<div class="mt-4 container-fluid vh-100 bg-white">
    <div class="justify-content-center row w-100 mt-5">
        <div class="col-md-10 table-responsible">
            <table class="table table-striped table-bordered table-hover table-sm">
                <thead>
                    <tr>
                        <th style="width: 10%;">ID</th>
                        <th style="width: 35%;">Produto</th>
                        <th style="width: 15%;">Valor</th>
                        <th style="width: 15%;">Quantidade</th>
                        <th style="width: 15%;">Valor total</th>
                        <th style="width: 5%;" colspan="1">Opções</th>
                    </tr>
                </thead>
                <tbody id="tabela_produtos">
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="" class="text-right">Valor total</th>
                        <th colspan="1" class="text-right" id="valor_total_geral">R$ 0,00</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <form action="{{ route('vendas.store') }}" method="post" id="finaliza" name="finaliza">
        @csrf
        @method('post')

        <input type="hidden" name="produtos" id="produtos_json">

        <div class="row">
            <div class="col-sm-11 d-flex justify-content-end">
                <button type="button" class="btn btn-success rounded-1" onclick="finalizarVenda()">Confirmar Venda</button>
            </div>
        </div>
    </form>
</div>
@endsection