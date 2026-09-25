

<?php
/**
 * Contrato base para los tipos de reto de Neureka GS.
 *
 * Cada tipo de reto (opción múltiple, arrastrar y soltar, completar espacios)
 * extiende esta clase. Nada en el plugin debe saber cómo funciona un tipo
 * por dentro: el motor de retos solo habla con estos métodos.
 *
 * @package Neureka_GS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Neureka_Abstract_Challenge_Type {

	/*
	 * ---------------------------------------------------------------
	 * Identidad del tipo
	 * ---------------------------------------------------------------
	 */

	/**
	 * Slug interno e inmutable del tipo. Se guarda en la base de datos,
	 * así que una vez publicado no se cambia.
	 *
	 * Ej: 'opcion_multiple', 'arrastrar', 'completar'
	 */
	abstract public function get_type(): string;

	/**
	 * Nombre legible para el admin. Este sí puede cambiar y se traduce.
	 */
	abstract public function get_label(): string;

	/*
	 * ---------------------------------------------------------------
	 * Configuración (lo que el docente guarda en el reto)
	 * ---------------------------------------------------------------
	 */

	/**
	 * Describe los campos que el admin muestra para configurar este tipo.
	 * No imprime HTML: devuelve una descripción de los campos y otra capa
	 * decide cómo pintarlos.
	 *
	 * Formato esperado: un array de arrays, cada uno con al menos
	 *   'key'      => string  identificador dentro del config
	 *   'label'    => string
	 *   'type'     => string  'text' | 'textarea' | 'repeater' | 'number' | 'bool' | ...
	 *   'required' => bool
	 *   'help'     => string  opcional
	 *
	 * @return array<int, array<string, mixed>>
	 */
	abstract public function get_admin_fields(): array;

	/**
	 * Revisa que el config guardado sea usable antes de dejar publicar el reto.
	 * Aquí van las reglas del tipo: que haya al menos dos opciones, que exista
	 * una respuesta correcta, que los índices existan, etc.
	 *
	 * @param array $config Configuración cruda del reto.
	 * @return true|WP_Error true si es válido, WP_Error con los detalles si no.
	 */
	abstract public function validate_config( array $config );

	/**
	 * Versión del config segura para enviar al navegador.
	 *
	 * Esta es la que evita el problema de exponer respuestas: el config
	 * completo vive en el servidor y al frontend solo viaja lo necesario
	 * para dibujar el reto.
	 *
	 * @param array $config Configuración completa.
	 * @return array Configuración sin respuestas ni pistas de evaluación.
	 */
	abstract public function get_public_config( array $config ): array;

	/*
	 * ---------------------------------------------------------------
	 * Presentación
	 * ---------------------------------------------------------------
	 */

	/**
	 * Devuelve el HTML del reto listo para insertar.
	 *
	 * Debe usar get_public_config() y nunca el config completo.
	 * Devuelve string en vez de imprimir, para poder cachear y testear.
	 *
	 * @param int   $challenge_id ID del CPT del reto.
	 * @param array $config       Configuración completa del reto.
	 * @return string HTML escapado.
	 */
	abstract public function render( int $challenge_id, array $config ): string;

	/*
	 * ---------------------------------------------------------------
	 * Respuesta del estudiante
	 * ---------------------------------------------------------------
	 */

	/**
	 * Convierte lo que llega del REST API en una estructura limpia y tipada.
	 * Es la única puerta de entrada de datos externos a este tipo de reto.
	 *
	 * No decide si la respuesta es correcta. Solo garantiza que tenga la forma
	 * esperada y que no traiga nada raro.
	 *
	 * @param mixed $raw Payload crudo de la petición.
	 * @return array Respuesta normalizada, o array vacío si no es utilizable.
	 */
	abstract public function sanitize_respuesta( $raw ): array;

	/**
	 * Califica una respuesta ya sanitizada contra el config completo.
	 *
	 * Debe ser una función pura: mismos argumentos, mismo resultado. No escribe
	 * en la base de datos, no toca sesiones, no dispara notificaciones. De eso
	 * se encarga la capa que registra el intento en neureka_intentos.
	 *
	 * @param array $respuesta Salida de sanitize_respuesta().
	 * @param array $config    Configuración completa del reto.
	 * @return array Ver make_result() para la forma del retorno.
	 */
	abstract public function evaluate( array $respuesta, array $config ): array;

	/*
	 * ---------------------------------------------------------------
	 * Ayudas compartidas
	 * ---------------------------------------------------------------
	 */

	/**
	 * Construye el resultado de evaluate() con una forma estable.
	 * Todos los tipos devuelven lo mismo para que el motor no tenga que
	 * preguntar de qué tipo era el reto.
	 *
	 * @param float $puntaje  Entre 0 y 1.
	 * @param array $extra    Claves opcionales: 'retroalimentacion', 'detalle'.
	 * @return array{correcto:bool, puntaje:float, retroalimentacion:string, detalle:array}
	 */
	protected function make_result( float $puntaje, array $extra = [] ): array {
		$puntaje = max( 0.0, min( 1.0, $puntaje ) );

		return [
			'correcto'          => $puntaje >= 1.0,
			'puntaje'           => $puntaje,
			'retroalimentacion' => isset( $extra['retroalimentacion'] ) ? (string) $extra['retroalimentacion'] : '',
			'detalle'           => isset( $extra['detalle'] ) && is_array( $extra['detalle'] ) ? $extra['detalle'] : [],
		];
	}

	/**
	 * Atajo para armar los WP_Error de validate_config() sin repetir el código.
	 *
	 * @param string $codigo
	 * @param string $mensaje
	 * @return WP_Error
	 */
	protected function config_error( string $codigo, string $mensaje ): WP_Error {
		return new WP_Error( 'neureka_config_' . $codigo, $mensaje );
	}
}