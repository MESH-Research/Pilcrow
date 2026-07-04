<?php
declare(strict_types=1);

namespace App\Listeners;

use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use Nuwave\Lighthouse\Events\ManipulateAST;

/**
 * Adds `implements Paginator` to all *Paginator object types
 * so that a single fragment can target the Paginator interface.
 */
class AddPaginatorInterface
{
    /**
     * Handle the ManipulateAST event.
     *
     * @param \Nuwave\Lighthouse\Events\ManipulateAST $event
     * @return void
     */
    public function handle(ManipulateAST $event): void
    {
        $paginatorInterface = Parser::parseType('Paginator');

        foreach ($event->documentAST->types as $type) {
            if (
                $type instanceof ObjectTypeDefinitionNode
                && str_ends_with($type->name->value, 'Paginator')
            ) {
                foreach ($type->interfaces as $iface) {
                    if ($iface->name->value === 'Paginator') {
                        continue 2;
                    }
                }
                $type->interfaces[] = $paginatorInterface;
            }
        }
    }
}
