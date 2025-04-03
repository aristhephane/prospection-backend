<?php

namespace App\Controller\Api;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class InterfaceController extends AbstractController
{
    /**
     * Récupère la structure du menu pour l'interface administrateur
     */
    #[Route('/interface/admin-menu', name: 'api_interface_admin_menu', methods: ['GET'])]
    public function getAdminInterface(): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        if (!$user || !$user->isAdministrateur()) {
            return new JsonResponse(['error' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }
        
        // Structure du menu administrateur
        $menu = [
            'items' => [
                [
                    'id' => 'dashboard',
                    'title' => 'Tableau de bord',
                    'icon' => 'dashboard',
                    'link' => '/admin/dashboard'
                ],
                [
                    'id' => 'fiches',
                    'title' => 'Gestion des fiches',
                    'icon' => 'description',
                    'link' => '/admin/fiches',
                    'children' => [
                        [
                            'id' => 'fiches-consultation',
                            'title' => 'Consultation des fiches',
                            'link' => '/admin/fiches/consultation'
                        ],
                        [
                            'id' => 'fiches-modification',
                            'title' => 'Modification des fiches',
                            'link' => '/admin/fiches/modification'
                        ],
                        [
                            'id' => 'fiches-suppression',
                            'title' => 'Suppression des fiches',
                            'link' => '/admin/fiches/suppression'
                        ],
                        [
                            'id' => 'fiches-generation',
                            'title' => 'Génération de nouvelles fiches',
                            'link' => '/admin/fiches/generation'
                        ],
                        [
                            'id' => 'fiches-historique',
                            'title' => 'Historique des fiches',
                            'link' => '/admin/fiches/historique'
                        ],
                        [
                            'id' => 'fiches-recherche',
                            'title' => 'Recherche multicritère',
                            'link' => '/admin/fiches/recherche'
                        ]
                    ]
                ],
                [
                    'id' => 'listings',
                    'title' => 'Module de génération de listings',
                    'icon' => 'list',
                    'link' => '/admin/listings',
                    'children' => [
                        [
                            'id' => 'listings-recherche',
                            'title' => 'Recherche multicritère',
                            'link' => '/admin/listings/recherche'
                        ],
                        [
                            'id' => 'listings-export',
                            'title' => 'Édition et exportation de listings',
                            'link' => '/admin/listings/export'
                        ]
                    ]
                ],
                [
                    'id' => 'tableau-bord',
                    'title' => 'Tableau de bord',
                    'icon' => 'analytics',
                    'link' => '/admin/tableau-bord',
                    'children' => [
                        [
                            'id' => 'tableau-bord-activites',
                            'title' => 'Vue synthétique des activités',
                            'link' => '/admin/tableau-bord/activites'
                        ],
                        [
                            'id' => 'tableau-bord-statistiques',
                            'title' => 'Accès aux statistiques et KPI',
                            'link' => '/admin/tableau-bord/statistiques'
                        ]
                    ]
                ],
                [
                    'id' => 'notifications',
                    'title' => 'Gestion des notifications',
                    'icon' => 'notifications',
                    'link' => '/admin/notifications',
                    'children' => [
                        [
                            'id' => 'notifications-liste',
                            'title' => 'Lister les notifications',
                            'link' => '/admin/notifications/liste'
                        ],
                        [
                            'id' => 'notifications-supprimer',
                            'title' => 'Supprimer les notifications',
                            'link' => '/admin/notifications/supprimer'
                        ]
                    ]
                ],
                [
                    'id' => 'utilisateurs',
                    'title' => 'Gestion des utilisateurs',
                    'icon' => 'people',
                    'link' => '/admin/utilisateurs',
                    'children' => [
                        [
                            'id' => 'utilisateurs-gestion',
                            'title' => 'Gestion des comptes utilisateurs',
                            'link' => '/admin/utilisateurs/gestion'
                        ],
                        [
                            'id' => 'utilisateurs-roles',
                            'title' => 'Gestion des droits et des rôles',
                            'link' => '/admin/utilisateurs/roles'
                        ]
                    ]
                ],
                [
                    'id' => 'database',
                    'title' => 'Gestion de la base de données',
                    'icon' => 'storage',
                    'link' => '/admin/database',
                    'children' => [
                        [
                            'id' => 'database-backup',
                            'title' => 'Sauvegardes et restaurations',
                            'link' => '/admin/database/backup'
                        ]
                    ]
                ],
                [
                    'id' => 'parametres',
                    'title' => 'Paramètres avancés',
                    'icon' => 'settings',
                    'link' => '/admin/parametres',
                    'children' => [
                        [
                            'id' => 'parametres-criteres',
                            'title' => 'Configuration des critères de recherche',
                            'link' => '/admin/parametres/criteres'
                        ],
                        [
                            'id' => 'parametres-secteurs',
                            'title' => 'Gestion des secteurs d\'activité',
                            'link' => '/admin/parametres/secteurs'
                        ]
                    ]
                ]
            ]
        ];
        
        return new JsonResponse($menu);
    }
    
    /**
     * Récupère la structure du menu pour l'interface utilisateur standard
     */
    #[Route('/interface/user-menu', name: 'api_interface_user_menu', methods: ['GET'])]
    public function getUserInterface(): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Authentification requise'], Response::HTTP_UNAUTHORIZED);
        }
        
        // Structure de base du menu utilisateur
        $menu = [
            'items' => [
                [
                    'id' => 'dashboard',
                    'title' => 'Tableau de bord',
                    'icon' => 'dashboard',
                    'link' => '/dashboard'
                ],
                [
                    'id' => 'fiches',
                    'title' => 'Gestion des fiches',
                    'icon' => 'description',
                    'link' => '/fiches',
                    'children' => [
                        [
                            'id' => 'fiches-consultation',
                            'title' => 'Consultation des fiches',
                            'link' => '/fiches/consultation'
                        ]
                    ]
                ],
                [
                    'id' => 'notifications',
                    'title' => 'Gestion des notifications',
                    'icon' => 'notifications',
                    'link' => '/notifications',
                    'children' => [
                        [
                            'id' => 'notifications-liste',
                            'title' => 'Lister les notifications',
                            'link' => '/notifications/liste'
                        ],
                        [
                            'id' => 'notifications-supprimer',
                            'title' => 'Supprimer les notifications',
                            'link' => '/notifications/supprimer'
                        ]
                    ]
                ],
                [
                    'id' => 'parametres',
                    'title' => 'Paramètres',
                    'icon' => 'settings',
                    'link' => '/parametres',
                    'children' => [
                        [
                            'id' => 'parametres-profil',
                            'title' => 'Gestion du profil utilisateur',
                            'link' => '/parametres/profil'
                        ],
                        [
                            'id' => 'parametres-application',
                            'title' => 'Paramètres de l\'application',
                            'link' => '/parametres/application'
                        ]
                    ]
                ]
            ]
        ];
        
        // Adapter le menu en fonction du rôle de l'utilisateur
        $roles = $user->getRoles();
        
        // Accès en modification aux fiches
        $roleAvecModificationFiches = array_intersect($roles, ['ROLE_PROSPECTION', 'ROLE_ADMINISTRATEUR', 'ROLE_ENSEIGNANT']);
        if (count($roleAvecModificationFiches) > 0) {
            $menu['items'][1]['children'][] = [
                'id' => 'fiches-modification',
                'title' => 'Modification des fiches',
                'link' => '/fiches/modification'
            ];
            
            // Uniquement pour certains rôles avec écriture
            if (in_array('ROLE_PROSPECTION', $roles) || in_array('ROLE_ADMINISTRATEUR', $roles)) {
                $menu['items'][1]['children'][] = [
                    'id' => 'fiches-generation',
                    'title' => 'Génération de nouvelles fiches',
                    'link' => '/fiches/generation'
                ];
                
                $menu['items'][1]['children'][] = [
                    'id' => 'fiches-historique',
                    'title' => 'Historique des fiches',
                    'link' => '/fiches/historique'
                ];
            }
        }
        
        // Accès aux rapports
        $roleAvecRapports = array_intersect($roles, ['ROLE_PROSPECTION', 'ROLE_RESPONSABLE', 'ROLE_ACADEMIQUE', 'ROLE_ORIENTATION', 'ROLE_ENSEIGNANT', 'ROLE_ADMINISTRATEUR']);
        if (count($roleAvecRapports) > 0) {
            $menu['items'][] = [
                'id' => 'rapports',
                'title' => 'Rapports',
                'icon' => 'assessment',
                'link' => '/rapports',
                'children' => [
                    [
                        'id' => 'rapports-statistiques',
                        'title' => 'Statistiques',
                        'link' => '/rapports/statistiques'
                    ],
                    [
                        'id' => 'rapports-listings',
                        'title' => 'Listings et exportations',
                        'link' => '/rapports/listings'
                    ]
                ]
            ];
        }
        
        return new JsonResponse($menu);
    }
} 