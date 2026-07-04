<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Listeners\AddPaginatorInterface;
use Nuwave\Lighthouse\Events\ManipulateAST;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Tests\TestCase;

/**
 * Coverage for the AddPaginatorInterface listener, which tags every
 * *Paginator object type with the Paginator interface so a single fragment can
 * target them all.
 */
class AddPaginatorInterfaceTest extends TestCase
{
    /**
     * The listener adds the Paginator interface to a *Paginator type that does
     * not already declare it.
     *
     * @return void
     */
    public function test_adds_interface_to_paginator_type(): void
    {
        $ast = DocumentAST::fromSource(<<<'GRAPHQL'
            interface Paginator { total: Int }
            type FooPaginator { total: Int }
            GRAPHQL);

        (new AddPaginatorInterface())->handle(new ManipulateAST($ast));

        $interfaces = $ast->types['FooPaginator']->interfaces;
        $this->assertCount(1, $interfaces);
        $this->assertEquals('Paginator', $interfaces[0]->name->value);
    }

    /**
     * The listener is idempotent: a *Paginator type that already implements
     * Paginator is left with a single interface rather than a duplicate.
     *
     * @return void
     */
    public function test_does_not_duplicate_existing_interface(): void
    {
        $ast = DocumentAST::fromSource(<<<'GRAPHQL'
            interface Paginator { total: Int }
            type FooPaginator implements Paginator { total: Int }
            GRAPHQL);

        (new AddPaginatorInterface())->handle(new ManipulateAST($ast));

        $interfaces = $ast->types['FooPaginator']->interfaces;
        $this->assertCount(1, $interfaces);
        $this->assertEquals('Paginator', $interfaces[0]->name->value);
    }

    /**
     * Non-Paginator types are left untouched.
     *
     * @return void
     */
    public function test_ignores_non_paginator_types(): void
    {
        $ast = DocumentAST::fromSource(<<<'GRAPHQL'
            interface Paginator { total: Int }
            type Widget { id: ID }
            GRAPHQL);

        (new AddPaginatorInterface())->handle(new ManipulateAST($ast));

        $this->assertCount(0, $ast->types['Widget']->interfaces);
    }
}
