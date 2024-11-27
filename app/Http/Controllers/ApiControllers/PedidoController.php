<?php

namespace App\Http\Controllers\ApiControllers;
use  App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoItem;
use  App\Events\NuevoPedido;
use App\Models\EstadoPedido;
use App\Models\Articulo;
class PedidoController extends Controller
{
    public function getEstadosPedido(){
        return EstadoPedido::all();
    }
    public function getPedido($id){
        $fecha = date('Y-m-d');
        $pedido = Pedido::with(['Items.Articulo'])
        ->with(['ProduccionPedidoHoy'])->find($id);
        return $pedido;
    }
    public function getPedidos(Request $request){
        $pedido = Pedido::with(['Items.Articulo','Cliente','ProduccionPedidoHoy']);
        if($request->start){
            $pedido->whereDate('fecha','>=',$request->start);
        }
        if($request->end){
            $pedido->whereDate('fecha','<=',$request->end);
        }
        if($request->busqueda){
            $pedido->where(function($query) use ($request){
                $query->where('nro','like','%'.$request->busqueda.'%');
                $query->orWhere('id','like','%'.$request->busqueda.'%');
                $query->orWhereHas('Cliente',function ($query) use($request){
                    $query->where('nombre_fiscal','like','%'.$request->busqueda.'%');
                    $query->orWhere('codigo_envio','like','%'.$request->busqueda.'%');
                });
            });
        }
        if($request->sort_by != null){
            $pedido->orderBy($request->sort_by,$request->sort_desc == 'false'?'ASC':'DESC');
        }
        else{
            $pedido->orderBy('id_prestashop', 'DESC');
        }
        return $pedido->paginate($request->cantidad??15);
    }
    public function setPedidoState(Request $request){
        $pedido = Pedido::find($request->id);
        if($pedido){
            $pedido->id_estado = $request->id_estado;

            if(!isset($pedido->fecha_empaquetado) && $request->id_estado == 2){
                $pedido->fecha_empaquetado = Carbon::parse($request->fecha_cambio_estado)->format('Y-m-d'); 
            }

            $pedido->update();
            $this->savePedidoItems($pedido,$request->items);
            event(new NuevoPedido());

            return Pedido::with('Items.Articulo')->find($request->id);
        }   
    }
    public function deletePedido($id){
        return Pedido::find($id)->delete();
    }
    public function savePedido(Request $request){
        if($request->fecha == null ){
            $request->fecha = date('Y-m-d H:i:s');
        }
        $pedido = Pedido::updateOrCreate(['id'=>$request->id], $request->all());
        $this->savePedidoItems($pedido,$request->items);
        try{
            event(new NuevoPedido());

        }catch(\Exception $ex){

        }
       
    }
    public function savePedidoItems($pedido,$items){
        $ids = [];
        foreach($items as $item){
            $cantidad_servida = $item['cantidad_servida']??0; // enviada en la request

            $articulo = Articulo::find($item['id_articulo']);
            $ca = $articulo?->unidades_caja??1; // unidades del articulo que conforman una caja 
            
            if($ca == 0) $ca = 1; // si las unidades_caja = 0 entonces pasar a 1 por defcto
            
            // Si el pedido esta pedniente entonces la cantidad srvida sera de 0, es decir,
            // falta entregar el total del pedido
            if($pedido->id_estado == 1){ 
                $cantidad_servida = 0;
            }

            // Si el pedido fue completado entonces la cantidad_servida (en cajas) 
            // sera la cantidad total del pedido multiplicando por articulo.unidades_caja 
            if($pedido->id_estado == 2){
                $cantidad_servida = $item['cantidad']*$ca;
            }

            if(($item['id']??null) != null){
                $pedidoItem = PedidoItem::find($item['id']);
                if($pedidoItem != null){
                    // Si el pedfido se ha empaquetado parcialmente entonces la cantidad servida sera 
                    // la cantidad servida del pedido multiplicado por articulo.unidades_caja
                    if($pedido->id_estado == 3){
                        $cantidad_und = intval($item['cantidad_servida'])*$ca;
                        $inventario = $cantidad_und - ($pedidoItem->cantidad_servida)*$ca;
                    }elseif($pedido->id_estado == 2){
                        $inventario = $cantidad_servida - ($pedidoItem->cantidad_servida)*$ca;
                    }else{
                        $inventario = $cantidad_servida - $pedidoItem->cantidad_servida;
                    }

                    $articulo = $pedidoItem->Articulo;
                    $articulo->cantidad -= $inventario;
                    $articulo->update();
                }
            }
            $item_obj = PedidoItem::updateOrCreate(['id'=>$item['id']??null],[
                'id_pedido'=>$pedido->id,
                'id_articulo'=>$item['id_articulo'],
                'cantidad'=>$item['cantidad'],
                'precio'=>$item['precio'],
                'sin_stock'=>$item['sin_stock']??0,
                'cantidad_servida'=>$cantidad_servida,
             
            ]);
            $ids[] = $item_obj->id;
        }
        PedidoItem::where('id_pedido',$pedido->id)->whereNotIn('id',$ids)->delete();
    }
}
