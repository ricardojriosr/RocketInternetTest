<?php
/**
 * Created by PhpStorm.
 * User: Ricardo
 * Date: 27-10-2015
 * Time: 12:24 PM
 */
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

class CashmachineController extends Controller
{

    /**
     * @Route("/Cashmachine/withdraw/{cantidad}", defaults={"cantidad" = 0}))
     **/
    public function withdrawAction($cantidad)
    {
        //Creo un arreglo con los billetes en orden
        $billetes = Array(100,50,20,10);

        //Creo arreglo vacio
        $aEntregar = Array();

        //Guardo la cantidad en un temporal
        $tmpCantidad = $cantidad;

        //Si es negativo arrojo error
        if($cantidad < 0)
        {
            throw new InvalidArgumentException("Cantidad invalida");
            return 0;
        }

        //Si es nulo o cero, regreso un array vacio que ya he inicializado
        if (($cantidad == null) || ($cantidad ==0))
        {
            return new JsonResponse($aEntregar);
            return 0;
        }

        // Contador
        $i = 0;

        //Empiezo un ciclo (lo ejecuto al menos una vez)
        do
        {
            //Aproximo el resultado de la division entre la cantidad y los billetes, comenzando por la mas alta denominacion
            $entero = floor($cantidad/$billetes[$i]);
            //Obtengo el residuo de la misma division anterior, para verificar si ya envie la cantidad requerida, o sigo en el ciclo
            $residuo = $cantidad%$billetes[$i];
            //Inserto el entero resultado de la aproximacion en el arreglo con array push
            array_push($aEntregar, $entero);
            //Coloco la cantidad dada igual que el residuo, para seguir en la proxima iteracion
            $cantidad = $residuo;
            //Aumento el contador
            $i++;

        } //Solo finalizo si el resudio es mayor a la cantidad de elementos del arreglo de billetes (-1)
        while($residuo >= $billetes[count($billetes)-1] );

        //Arrojo un error si el residuo es diferente de cero debido a que no es multiplo de 10
        if($residuo!=0)
        {
            throw new NoteUnavailableException('No puede ser entregada la cantidad ingresada'); //Error Here
            return 0;
        }

        //Coloco cero, en los elementos vacios
        if(count($aEntregar) < count($billetes))
        {
            do
            {
                array_push($aEntregar, 0);
            }
            //Mientras las cantidades en los arreglos sean diferentes, ejecuto el ciclo
            while(count($aEntregar) < count($billetes));
        }

        //Multiplico la cantidad por los billetes segun el orden del arreglo
        for ($j = 0; $j < count($billetes); $j++)
        {
            $tmpVar = ($aEntregar[$j] * $billetes[$j]);
            //Le coloco dos decimales al resultado
            $aEntregar[$j] = number_format($tmpVar, 2, '.', '');
        }

        //Regreso el arreglo
        $data = array(
            'La cantidad es' => $tmpCantidad,
            'A entregar es' => $aEntregar
        );

        // calls json_encode and sets the Content-Type header
        return new JsonResponse($data);
    }
}